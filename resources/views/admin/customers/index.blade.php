@extends('layouts.admin')

@section('admin_content')
<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Clientes Registrados</h2>

    <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-0 border-dark-subtle" placeholder="Nombre o correo" aria-label="Buscar cliente">
        <button type="submit" class="btn btn-sm btn-dark rounded-0 px-3" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-0 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3 text-start">Cliente</th>
                        <th class="py-3">Registro</th>
                        <th class="py-3">Pedidos</th>
                        <th class="py-3">Total Comprado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="text-start">
                                <span class="d-block fw-bold">{{ $customer->name }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $customer->email }}</span>
                            </td>
                            <td class="text-muted">{{ $customer->created_at?->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ $customer->orders_count }}</td>
                            <td class="fw-bold">S/ {{ number_format($customer->total_spent ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted py-4">
                                {{ request('q') ? 'Ningún cliente coincide con la búsqueda.' : 'Aún no hay clientes registrados.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $customers->links() }}
</div>
@endsection
