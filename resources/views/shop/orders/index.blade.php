@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Mis pedidos')

@section('content')
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="mb-4">
                <h1 class="h2 m-0">Mis pedidos</h1>
                <p class="text-muted small">Historial de compras realizadas en TEZAN.</p>
            </div>

            <div class="card border border-dark bg-white">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center small">
                            <thead class="table-light text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="py-3">N° Referencia</th>
                                    <th class="py-3">Fecha</th>
                                    <th class="py-3">Total</th>
                                    <th class="py-3">Estado de Envío</th>
                                    <th class="py-3">Pago</th>
                                    <th class="py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $order->reference_number }}</td>
                                        <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="fw-bold">S/ {{ number_format($order->total_amount, 2) }}</td>
                                        <td>@include('partials.order-badges', ['order' => $order, 'only' => 'status'])</td>
                                        <td>@include('partials.order-badges', ['order' => $order, 'only' => 'payment'])</td>
                                        <td>
                                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark px-3" style="font-size: 0.7rem;">
                                                Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted py-5 text-center">
                                            <i class="bi bi-bag-x d-block fs-1 text-muted mb-2"></i>
                                            Aún no has realizado ningún pedido.
                                            <div class="mt-3">
                                                <a href="{{ route('home') }}" class="btn btn-dark btn-sm px-4">Ver el catálogo</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
