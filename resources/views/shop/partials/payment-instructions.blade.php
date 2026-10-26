{{-- Cómo completar el pago de un pedido aún no pagado, según su medio de pago. Recibe $order. --}}
@if($order->payment_method === 'mercadopago')
    <div class="p-4 mb-4 text-start" style="background: var(--tz-warm, #f3f2ef);">
        <p class="tz-label mb-2">Pago con Mercado Pago</p>
        @if($order->payment_status === 'failed')
            <p class="mb-3">Mercado Pago rechazó el último intento de pago. Puedes intentarlo de nuevo con otra tarjeta o medio.</p>
        @else
            <p class="mb-3">Si ya pagaste, la confirmación puede tardar unos minutos y te avisaremos por correo. Si aún no pagaste, complétalo aquí.</p>
        @endif
        <p class="fs-4 fw-semibold tz-price mb-3">S/ {{ number_format($order->total_amount, 2) }}</p>
        <a href="{{ route('checkout.mercadopago.pay', $order->reference_number) }}" class="btn btn-dark">
            {{ $order->payment_status === 'failed' ? 'Intentar el pago de nuevo' : 'Pagar con Mercado Pago' }}
        </a>
    </div>
@else
    @include('shop.partials.yape-instructions', ['order' => $order])
@endif
