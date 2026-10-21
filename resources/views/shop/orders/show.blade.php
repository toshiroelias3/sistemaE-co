@extends('layouts.app')

@section('title', 'Pedido ' . $order->reference_number)

@section('content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-light text-uppercase tracking-wider m-0">Pedido {{ $order->reference_number }}</h1>
                    <span class="text-muted small">Realizado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }}</span>
                </div>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-0 text-uppercase tracking-wider px-3">Volver</a>
            </div>

            <!-- Línea de seguimiento -->
            @php
                $steps = ['processing' => 'Procesando', 'shipped' => 'En ruta', 'completed' => 'Entregado'];
                $currentStep = array_search($order->status, array_keys($steps));
            @endphp
            @if($order->status === 'cancelled')
                <div class="alert alert-danger rounded-0 small text-uppercase tracking-wider mb-4">
                    <i class="bi bi-x-circle me-2"></i> Este pedido fue cancelado.
                </div>
            @else
                <div class="d-flex mb-4 text-center small text-uppercase tracking-wider">
                    @foreach($steps as $key => $label)
                        @php $done = $currentStep !== false && $loop->index <= $currentStep; @endphp
                        <div class="flex-fill">
                            <div class="mb-2" style="height: 4px; background-color: {{ $done ? '#1a1a1a' : '#e9ecef' }};"></div>
                            <span class="{{ $done ? 'text-dark fw-bold' : 'text-muted' }}" style="font-size: 0.7rem;">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="row g-4">
                <div class="col-12 col-md-8">
                    <div class="card border-0 shadow-sm rounded-0 bg-white p-4 mb-4">
                        <h2 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Prendas de tu Orden</h2>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0 small">
                                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.7rem;">
                                    <tr>
                                        <th>Prenda</th>
                                        <th class="text-center">Variante</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr class="border-bottom">
                                            <td class="fw-bold text-dark py-3">{{ $item->product->name ?? 'Producto no disponible' }}</td>
                                            <td class="text-center text-muted">
                                                Talla: {{ $item->variant->size->name ?? 'N/A' }} | Color: {{ $item->variant->color->name ?? 'N/A' }}
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end fw-bold text-dark">S/ {{ number_format($item->price * $item->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="3" class="text-end text-muted pt-3">Envío:</td>
                                        <td class="text-end pt-3">{{ $order->shipping_cost > 0 ? 'S/ ' . number_format($order->shipping_cost, 2) : 'Gratis' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end text-uppercase tracking-wider fw-bold text-muted">Total Abonado:</td>
                                        <td class="text-end fw-bold text-dark fs-5">S/ {{ number_format($order->total_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm rounded-0 bg-white p-4 mb-3">
                        <h2 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Dirección de Envío</h2>
                        <p class="small text-dark mb-1"><i class="bi bi-geo-alt me-1"></i> {{ $order->shipping_address }}</p>
                        <p class="small text-muted mb-0"><i class="bi bi-telephone me-1"></i> Teléfono: {{ $order->phone }}</p>
                    </div>

                    <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
                        <h2 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Seguimiento</h2>
                        <p class="small mb-2 text-uppercase tracking-wider fw-medium">
                            Estado: @include('partials.order-badges', ['order' => $order, 'only' => 'status'])
                        </p>
                        <p class="small m-0 text-uppercase tracking-wider fw-medium">
                            Pago: @include('partials.order-badges', ['order' => $order, 'only' => 'payment'])
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
