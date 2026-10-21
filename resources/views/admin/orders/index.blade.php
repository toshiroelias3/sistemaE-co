@extends('layouts.admin')

@section('admin_content')
<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Órdenes de Compra</h2>

    <!-- Filtros de búsqueda -->
    <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-0 border-dark-subtle" placeholder="Referencia, cliente o correo" aria-label="Buscar pedido">
        <select name="status" class="form-select form-select-sm rounded-0 border-dark-subtle" onchange="this.form.submit()" aria-label="Filtrar por estado" style="min-width: 150px;">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\Order::STATUSES as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-dark rounded-0 px-3" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-0 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3">Referencia</th>
                        <th class="py-3 text-start">Cliente</th>
                        <th class="py-3">Fecha</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Despacho</th>
                        <th class="py-3">Pago</th>
                        <th class="py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-bold text-dark">{{ $order->reference_number }}</td>
                            <td class="text-start">
                                <span class="d-block fw-bold">
                                    {{ $order->customer_name ?? $order->user->name }}
                                    @if(!$order->user_id)
                                        <span class="badge bg-secondary-subtle text-secondary rounded-0 ms-1" style="font-size: 0.6rem;">Invitado</span>
                                    @endif
                                </span>
                                <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $order->customer_email ?? $order->user->email }}</span>
                            </td>
                            <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="fw-bold">S/ {{ number_format($order->total_amount, 2) }}</td>
                            <td>@include('partials.order-badges', ['order' => $order, 'only' => 'status'])</td>
                            <td>@include('partials.order-badges', ['order' => $order, 'only' => 'payment'])</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-dark rounded-0 text-uppercase tracking-wider px-3" style="font-size: 0.7rem;">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4">
                                {{ request('q') || request('status') ? 'Ningún pedido coincide con los filtros.' : 'No se han registrado órdenes en la tienda todavía.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endsection
