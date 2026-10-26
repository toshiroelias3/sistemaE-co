<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Support\Cart;
use App\Support\MercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * Mostrar la pantalla de Checkout
     */
    public function index()
    {
        $cart = Cart::items();

        // Si intentan entrar al checkout con el carrito vacío, los regresamos al home
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu bolsa de compras está vacía.');
        }

        $subtotal = Cart::subtotal();
        $shippingCost = Cart::shippingFor($subtotal);

        return view('shop.checkout', compact('cart', 'subtotal', 'shippingCost'));
    }

    /**
     * Muestra la pantalla de confirmación/agradecimiento de la orden
     */
    public function success($reference_number)
    {
        $order = Order::where('reference_number', $reference_number)
            ->with(['items.product.images', 'items.variant.size', 'items.variant.color'])
            ->firstOrFail();

        return view('shop.checkout-success', compact('order'));
    }

    /**
     * Procesar la orden con Transacciones SQL
     */
    public function store(Request $request)
    {
        // 1. Validaciones del Formulario de Checkout
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'shipping_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s-]{6,30}$/'],
            'payment_method' => ['required', Rule::in(MercadoPago::isEnabled() ? ['yape', 'mercadopago'] : ['yape'])],
            'terms' => 'accepted',
        ], [
            'phone.regex' => 'Ingresa un número de teléfono válido.',
            'terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ]);

        $cart = Cart::items();
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu bolsa de compras está vacía.');
        }

        // Los clientes autenticados compran siempre con los datos de su cuenta
        $customerName = Auth::check() ? Auth::user()->name : strip_tags($request->customer_name);
        $customerEmail = Auth::check() ? Auth::user()->email : strip_tags($request->customer_email);

        // Concatenar dirección exacta con la ciudad/distrito
        $fullAddress = strip_tags($request->shipping_address).' - '.strip_tags($request->city);

        // 2. Iniciamos transacción para asegurar consistencia e integridad de datos
        DB::beginTransaction();

        try {
            // A) Bloquear el inventario de cada prenda y tomar el precio vigente de la base de datos
            //    (evita condiciones de carrera y precios desactualizados guardados en la sesión)
            $lines = [];
            foreach ($cart as $item) {
                $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                if (! $variant || ! $variant->product || ! $variant->product->is_active) {
                    throw new \RuntimeException("La prenda {$item['name']} ya no está disponible. Retírala de tu bolsa para continuar.");
                }
                if ($variant->stock < $item['quantity']) {
                    throw new \RuntimeException("Lo sentimos, la prenda {$item['name']} se ha quedado sin stock suficiente mientras procesabas tu compra.");
                }

                $lines[] = ['variant' => $variant, 'quantity' => (int) $item['quantity'], 'price' => $variant->product->base_price];
            }

            // B) Cálculo del Subtotal y Envío
            $subtotal = array_sum(array_map(fn ($line) => $line['price'] * $line['quantity'], $lines));
            $totalAmount = $subtotal + Cart::shippingFor($subtotal);

            // C) Crear la cabecera del Pedido (Soporta Autenticados e Invitados)
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'reference_number' => 'TZN-'.date('Ymd').'-'.strtoupper(Str::random(12)),
                'total_amount' => $totalAmount,
                'shipping_address' => $fullAddress,
                'phone' => strip_tags($request->phone),
                'status' => 'pending', // Pasa a "procesando" cuando se confirma el pago
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
            ]);

            // D) Registrar los ítems y restar stock real
            foreach ($lines as $line) {
                $line['variant']->decrement('stock', $line['quantity']);

                $order->items()->create([
                    'product_id' => $line['variant']->product_id,
                    'product_variant_id' => $line['variant']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                ]);
            }

            DB::commit();

            // E) Limpiar el carrito de la sesión tras la compra exitosa
            session()->forget('cart');

            // F) Correo con el resumen y las instrucciones de Yape (si falla, el pedido igual queda registrado)
            try {
                Mail::to($order->customer_email)->send(new OrderPlaced($order->load('items.product', 'items.variant.size', 'items.variant.color')));
            } catch (\Throwable $e) {
                Log::error("No se pudo enviar la confirmación del pedido {$order->reference_number}: {$e->getMessage()}");
            }

            // G) Con Mercado Pago, el cliente pasa a pagar en su página; con Yape o Plin, ve las instrucciones
            if ($order->payment_method === 'mercadopago') {
                return $this->redirectToMercadoPago($order);
            }

            return redirect()->route('checkout.success', $order->reference_number);

        } catch (\RuntimeException $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al procesar checkout: '.$e->getMessage());

            return back()->withInput()->with('error', 'No pudimos procesar tu pedido. Inténtalo nuevamente en unos minutos.');
        }
    }

    /**
     * Reintenta (o retoma) el pago con Mercado Pago de un pedido aún no pagado
     */
    public function payWithMercadoPago(string $reference_number)
    {
        $order = Order::where('reference_number', $reference_number)->firstOrFail();

        if ($order->payment_method !== 'mercadopago' || $order->payment_status === 'paid' || $order->status === 'cancelled') {
            return redirect()->route('checkout.success', $order->reference_number);
        }

        return $this->redirectToMercadoPago($order);
    }

    /**
     * El cliente vuelve desde Mercado Pago. El estado se consulta a la API; los parámetros de la URL no se toman como prueba de pago.
     */
    public function mercadoPagoReturn(Request $request, string $reference_number)
    {
        $order = Order::where('reference_number', $reference_number)->firstOrFail();
        $paymentId = $request->query('payment_id') ?? $request->query('collection_id');

        if ($paymentId && MercadoPago::isEnabled()) {
            $synced = MercadoPago::syncPayment((string) $paymentId);
            $order = $synced && $synced->is($order) ? $synced : $order->fresh();
        }

        $message = match (true) {
            $order->payment_status === 'paid' => ['success', '¡Pago aprobado! Ya estamos preparando tu pedido.'],
            $order->payment_status === 'failed' => ['error', 'Mercado Pago rechazó el pago. Puedes intentarlo de nuevo con otro medio.'],
            default => ['success', 'Recibimos tu pedido. Te avisaremos por correo cuando Mercado Pago confirme el pago.'],
        };

        return redirect()->route('checkout.success', $order->reference_number)->with(...$message);
    }

    private function redirectToMercadoPago(Order $order)
    {
        try {
            return redirect()->away(MercadoPago::createPreference($order));
        } catch (\Throwable $e) {
            Log::error("Mercado Pago no disponible para el pedido {$order->reference_number}: {$e->getMessage()}");

            return redirect()->route('checkout.success', $order->reference_number)
                ->with('error', 'No pudimos conectar con Mercado Pago. Tu pedido está reservado: intenta pagar de nuevo en unos minutos o escríbenos.');
        }
    }
}
