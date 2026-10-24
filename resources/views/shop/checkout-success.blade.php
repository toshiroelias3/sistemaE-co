@extends('layouts.app')

@section('noindex', '1')

@section('title', '¡Gracias por tu compra!')

@section('content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Header de Agradecimiento -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
                <h1 class="fw-bold mb-2">¡Gracias por tu compra!</h1>
                <p class="text-muted lead">Tu pedido ha sido registrado con éxito y ya lo estamos preparando.</p>
                <div class="badge bg-light text-dark border p-2 px-3 font-monospace">
                    N° de Orden: {{ $order->reference_number }}
                </div>
            </div>

            <!-- Detalles del Pedido -->
            <div class="card border border-dark p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-4 border-bottom pb-2">Resumen de la Compra</h5>

                <div class="mb-4">
                    @foreach($order->items as $item)
                        <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                            <div style="width: 60px; height: 75px; background-color: #e9ecef;" class="flex-shrink-0">
                                @php
                                    $primaryImage = $item->product->images->where('is_primary', true)->first() ?? $item->product->images->first();
                                @endphp
                                @if($primaryImage)
                                    <img src="{{ asset('storage/' . $primaryImage->image_path) }}" class="w-100 h-100 object-fit-cover" alt="{{ $item->product->name }}">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-bag-check"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="small mb-1">{{ $item->product->name }}</h6>
                                <span class="d-block text-muted small">
                                    Talla: {{ $item->variant->size->name ?? 'N/A' }} | Color: {{ $item->variant->color->name ?? 'N/A' }}
                                </span>
                                <span class="small fw-semibold text-dark">{{ $item->quantity }} x S/ {{ number_format($item->price, 2) }}</span>
                            </div>
                            <span class="fw-bold small">S/ {{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Desglose de Totales -->
                <div class="bg-light p-3 border mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">S/ {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Envío</span>
                        <span class="fw-bold">{{ $order->shipping_cost > 0 ? 'S/ ' . number_format($order->shipping_cost, 2) : 'Gratis' }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fw-bold h5 mb-0">Total Pagado</span>
                        <span class="fw-bold text-dark h5 mb-0">S/ {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Información de Envío y Cliente -->
                <div class="row g-4 border-top pt-3">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold small text-muted mb-2">Datos del Cliente</h6>
                        <p class="small mb-1"><strong>Nombre:</strong> {{ $order->customer_name ?? $order->user->name }}</p>
                        <p class="small mb-0"><strong>Correo:</strong> {{ $order->customer_email ?? $order->user->email }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold small text-muted mb-2">Dirección de Entrega</h6>
                        <p class="small mb-1"><strong>Dirección:</strong> {{ $order->shipping_address }}</p>
                        <p class="small mb-0"><strong>Teléfono:</strong> {{ $order->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="{{ route('home') }}" class="btn btn-dark fw-bold py-3 px-4">
                    Volver a la Tienda
                </a>
                @auth
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-dark fw-bold py-3 px-4">
                        Ver Mis Pedidos
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection