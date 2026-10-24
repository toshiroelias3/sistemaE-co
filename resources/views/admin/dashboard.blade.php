@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Resumen Operativo</h2>
</div>

@if($usesDefaultPassword)
    <div class="alert alert-danger rounded-0 small">
        <i class="bi bi-shield-exclamation me-2"></i><strong>Tu cuenta usa la contraseña por defecto.</strong>
        Cámbiala antes de publicar la tienda: <code>php artisan tinker</code> y luego
        <code>User::where('email', '{{ Auth::user()->email }}')->first()->update(['password' => 'tu-nueva-clave'])</code>.
    </div>
@endif

@if($pendingPayments)
    <div class="alert alert-warning rounded-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-phone me-2"></i><strong>{{ $pendingPayments }} {{ $pendingPayments === 1 ? 'pedido espera' : 'pedidos esperan' }}</strong> que valides su pago por Yape.</span>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-dark rounded-0">Revisar pagos</a>
    </div>
@endif

<div class="row g-4 mb-5">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-0 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase tracking-wider small mb-1" style="font-size: 0.7rem;">Ventas Cobradas</h6>
                    <h3 class="fw-bold m-0 text-dark">S/. {{ number_format($totalEarnings, 2) }}</h3>
                </div>
                <div class="bg-light p-3 text-dark fs-3 border"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-0 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase tracking-wider small mb-1" style="font-size: 0.7rem;">Pedidos Recibidos</h6>
                    <h3 class="fw-bold m-0 text-dark">{{ $totalOrders }}</h3>
                </div>
                <div class="bg-light p-3 text-dark fs-3 border"><i class="bi bi-bag-check"></i></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-0 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase tracking-wider small mb-1" style="font-size: 0.7rem;">Modelos de Ropa</h6>
                    <h3 class="fw-bold m-0 text-dark">{{ $totalProducts }}</h3>
                </div>
                <div class="bg-light p-3 text-dark fs-3 border"><i class="bi bi-shirt"></i></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-0 bg-white">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase tracking-wider small mb-1" style="font-size: 0.7rem;">Clientes Registrados</h6>
                    <h3 class="fw-bold m-0 text-dark">{{ $totalClients }}</h3>
                </div>
                <div class="bg-light p-3 text-dark fs-3 border"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-0 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-uppercase tracking-wider small">Últimas Órdenes Registradas</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark rounded-0 px-3 text-uppercase small">Gestionar Pedidos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center small">
                        <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="py-3">Referencia</th>
                                <th class="py-3 text-start">Cliente</th>
                                <th class="py-3">Fecha / Hora</th>
                                <th class="py-3">Monto Total</th>
                                <th class="py-3">Estado Logístico</th>
                                <th class="py-3"><span class="visually-hidden">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $order->reference_number }}</td>
                                    <td class="text-start">
                                        <span class="d-block fw-bold">{{ $order->user->name }}</span>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $order->user->email }}</span>
                                    </td>
                                    <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold">S/. {{ number_format($order->total_amount, 2) }}</td>
                                    <td>@include('partials.order-badges', ['order' => $order, 'only' => 'status'])</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted py-4">No hay actividad comercial registrada en las últimas horas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de Inventario -->
<div class="card border-0 shadow-sm rounded-0 bg-white mt-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="m-0 fw-bold text-uppercase tracking-wider small"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Stock Bajo (3 unidades o menos)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3 text-start">Prenda</th>
                        <th class="py-3">SKU</th>
                        <th class="py-3">Talla / Color</th>
                        <th class="py-3">Stock</th>
                        <th class="py-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockVariants as $variant)
                        <tr>
                            <td class="text-start fw-bold">{{ $variant->product->name }}</td>
                            <td class="text-muted">{{ $variant->sku }}</td>
                            <td>{{ $variant->size->name ?? 'N/A' }} / {{ $variant->color->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge rounded-0 px-2 {{ $variant->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $variant->stock }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $variant->product_id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 text-uppercase" style="font-size: 0.7rem;">Reponer</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted py-4">Todo el inventario tiene stock suficiente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection