<?php

namespace App\Support;

use App\Mail\PaymentConfirmed;
use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Integración con Mercado Pago Checkout Pro vía su API REST.
 *
 * El estado de un pago nunca se toma de lo que manda el navegador: siempre se
 * consulta a la API de Mercado Pago con el access token antes de marcar un pedido.
 */
class MercadoPago
{
    private const API = 'https://api.mercadopago.com';

    public static function isEnabled(): bool
    {
        return filled(config('services.mercadopago.access_token'));
    }

    /**
     * Crea la preferencia de pago del pedido y devuelve la URL de Mercado Pago a la que se envía al cliente.
     *
     * @throws RuntimeException si Mercado Pago no responde correctamente
     */
    public static function createPreference(Order $order): string
    {
        $order->loadMissing('items.product', 'items.variant.size', 'items.variant.color');

        $items = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_variant_id,
            'title' => trim(($item->product->name ?? 'Prenda TEZAN').' - Talla '.($item->variant->size->name ?? '').', '.($item->variant->color->name ?? '')),
            'quantity' => (int) $item->quantity,
            'unit_price' => round((float) $item->price, 2),
            'currency_id' => 'PEN',
        ])->values()->all();

        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'envio',
                'title' => 'Envío',
                'quantity' => 1,
                'unit_price' => round($order->shipping_cost, 2),
                'currency_id' => 'PEN',
            ];
        }

        $returnUrl = route('checkout.mercadopago.return', $order->reference_number);
        $payload = [
            'items' => $items,
            'payer' => ['name' => $order->customer_name, 'email' => $order->customer_email],
            'external_reference' => $order->reference_number,
            'statement_descriptor' => 'TEZAN',
            'back_urls' => ['success' => $returnUrl, 'failure' => $returnUrl, 'pending' => $returnUrl],
        ];

        // Mercado Pago solo acepta volver solo y avisar por webhook a direcciones públicas con https
        if (str_starts_with($returnUrl, 'https://')) {
            $payload['auto_return'] = 'approved';
            $payload['notification_url'] = route('webhooks.mercadopago');
        }

        $response = self::client()->post('/checkout/preferences', $payload);

        if ($response->failed() || ! $response->json('id')) {
            throw new RuntimeException('Mercado Pago no pudo crear el pago: '.$response->body());
        }

        // Con credenciales de prueba se usa el entorno sandbox
        $isTest = str_starts_with((string) config('services.mercadopago.access_token'), 'TEST-');
        $initPoint = ($isTest ? $response->json('sandbox_init_point') : null) ?? $response->json('init_point');

        $order->update([
            'mp_preference_id' => $response->json('id'),
            'mp_init_point' => $initPoint,
        ]);

        return $initPoint;
    }

    /**
     * Consulta un pago en Mercado Pago y actualiza el pedido al que pertenece.
     * Es idempotente: si el pago ya se registró, no vuelve a enviar correos.
     */
    public static function syncPayment(string $paymentId): ?Order
    {
        $response = self::client()->get('/v1/payments/'.rawurlencode($paymentId));

        if ($response->failed()) {
            Log::warning("Mercado Pago: no se pudo consultar el pago {$paymentId}", ['status' => $response->status()]);

            return null;
        }

        $payment = $response->json();
        $justConfirmed = false;

        $order = DB::transaction(function () use ($payment, &$justConfirmed) {
            $order = Order::where('reference_number', $payment['external_reference'] ?? '')->lockForUpdate()->first();
            if (! $order) {
                return null;
            }

            $status = $payment['status'] ?? '';
            $changes = ['mp_payment_id' => (string) $payment['id']];

            if ($status === 'approved') {
                $amountMatches = ($payment['currency_id'] ?? '') === 'PEN'
                    && (float) ($payment['transaction_amount'] ?? 0) + 0.01 >= (float) $order->total_amount;

                if (! $amountMatches) {
                    Log::warning("Mercado Pago: el pago {$payment['id']} no coincide con el total del pedido {$order->reference_number}");
                } elseif ($order->payment_status !== 'paid') {
                    $changes['payment_status'] = 'paid';
                    $changes['status'] = $order->status === 'pending' ? 'processing' : $order->status;
                    $justConfirmed = true;
                }
            } elseif (in_array($status, ['rejected', 'cancelled'], true) && $order->payment_status === 'unpaid') {
                $changes['payment_status'] = 'failed'; // El cliente puede reintentar desde su pedido
            } elseif (in_array($status, ['refunded', 'charged_back'], true)) {
                $changes['payment_status'] = 'refunded';
            }

            $order->update($changes);

            return $order;
        });

        if ($order && $justConfirmed) {
            try {
                Mail::to($order->customer_email)->send(new PaymentConfirmed($order));
            } catch (\Throwable $e) {
                Log::error("No se pudo enviar la confirmación de pago del pedido {$order->reference_number}: {$e->getMessage()}");
            }
        }

        return $order;
    }

    /**
     * Valida la firma x-signature de las notificaciones de Mercado Pago.
     * Si no hay clave secreta configurada se acepta (la consulta a la API sigue protegiendo el estado).
     */
    public static function hasValidSignature(Request $request, string $dataId): bool
    {
        $secret = config('services.mercadopago.webhook_secret');
        if (blank($secret)) {
            return true;
        }

        $parts = collect(explode(',', (string) $request->header('x-signature')))
            ->mapWithKeys(function ($part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

                return [$key => $value];
            });

        if (! $parts->get('ts') || ! $parts->get('v1')) {
            return false;
        }

        $manifest = 'id:'.strtolower($dataId).';request-id:'.$request->header('x-request-id').';ts:'.$parts->get('ts').';';

        return hash_equals(hash_hmac('sha256', $manifest, $secret), $parts->get('v1'));
    }

    private static function client(): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()
            ->timeout(15);
    }
}
