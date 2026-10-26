<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\MercadoPago;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Recibe las notificaciones de Mercado Pago y sincroniza el pedido consultando el pago a su API.
 */
class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $type = $request->input('type') ?? $request->query('topic');
        $paymentId = (string) ($request->input('data.id') ?? $request->query('data_id') ?? $request->query('id') ?? '');

        // Solo interesan las notificaciones de pagos; el resto se confirma para que no se reintenten
        if ($type !== 'payment' || $paymentId === '' || ! MercadoPago::isEnabled()) {
            return response('', 200);
        }

        if (! MercadoPago::hasValidSignature($request, $request->query('data_id', $paymentId))) {
            return response('Firma inválida', 401);
        }

        MercadoPago::syncPayment($paymentId);

        return response('', 200);
    }
}
