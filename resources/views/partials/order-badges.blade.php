{{-- Etiquetas de estado de un pedido. Uso: @include('partials.order-badges', ['order' => $order, 'only' => 'status'|'payment']) --}}
@php
    $statusClasses = [
        'pending'    => 'bg-secondary-subtle text-secondary-emphasis',
        'processing' => 'bg-warning-subtle text-warning-emphasis',
        'shipped'    => 'bg-info-subtle text-info-emphasis',
        'completed'  => 'bg-success-subtle text-success-emphasis',
        'cancelled'  => 'bg-danger-subtle text-danger-emphasis',
    ];
    $paymentClasses = [
        'paid'     => 'bg-success text-white',
        'unpaid'   => 'bg-danger text-white',
        'failed'   => 'bg-danger text-white',
        'refunded' => 'bg-secondary text-white',
    ];
    $only = $only ?? 'status';
@endphp

@if($only === 'status')
    <span class="badge rounded-0 px-2 text-uppercase {{ $statusClasses[$order->status] ?? 'bg-secondary-subtle text-muted' }}">{{ $order->status_label }}</span>
@else
    <span class="badge rounded-0 px-2 text-uppercase {{ $paymentClasses[$order->payment_status] ?? 'bg-secondary text-white' }}" style="font-size: 0.65rem;">{{ $order->payment_status_label }}</span>
@endif
