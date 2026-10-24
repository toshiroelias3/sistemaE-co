@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Catálogo de Prendas</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-dark rounded-0 text-uppercase small tracking-wider px-4">
        <i class="bi bi-plus-lg me-2"></i> Nuevo Producto
    </a>
</div>

<div class="card border-0 shadow-sm rounded-0 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3">ID</th>
                        <th class="py-3 text-start">Prenda</th>
                        <th class="py-3 text-start">Categoría</th>
                        <th class="py-3">Precio Base</th>
                        <th class="py-3">Stock</th>
                        <th class="py-3">Destacada</th>
                        <th class="py-3">Estado</th>
                        <th class="py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="fw-bold">#{{ $product->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    @php $cover = $product->images->firstWhere('is_primary', true) ?? $product->images->first(); @endphp
                                    @if($cover)
                                        <img src="{{ asset('storage/' . $cover->image_path) }}" class="object-fit-cover border" width="45" height="45" alt="{{ $product->name }}">
                                    @else
                                        <div class="bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;">
                                            <i class="bi bi-image" style="font-size: 1.2rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="d-block fw-bold text-dark">{{ $product->name }}</span>
                                        <span class="text-muted text-lowercase" style="font-size: 0.75rem;">{{ $product->slug }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="text-start">
                                <span class="d-block fw-bold text-dark">{{ $product->category->name }}</span>
                                <span class="badge bg-light text-dark border rounded-0" style="font-size: 0.65rem;">{{ \App\Models\Product::GENDERS[$product->gender] ?? $product->gender }}</span>
                                @if($product->category->parent)
                                    <span class="text-muted text-uppercase fw-medium tracking-wide" style="font-size: 0.65rem;">
                                        Colección: {{ $product->category->parent->name }}
                                    </span>
                                @else
                                    <span class="text-muted text-uppercase tracking-wide" style="font-size: 0.65rem;">
                                        Línea Principal
                                    </span>
                                @endif
                            </td>

                            <td class="fw-bold">S/ {{ number_format($product->base_price, 2) }}</td>
                            <td>
                                <span class="badge rounded-0 px-2 {{ (int) $product->total_stock === 0 ? 'bg-danger' : ($product->total_stock <= 5 ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                    {{ (int) $product->total_stock }}
                                </span>
                            </td>
                            <td>
                                @if($product->is_featured)
                                    <i class="bi bi-star-fill text-warning" title="Prenda destacada"></i>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success-subtle text-success rounded-0 px-2 text-uppercase" style="font-size: 0.65rem;">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-0 px-2 text-uppercase" style="font-size: 0.65rem;">Inactivo</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1" title="Modificar Prenda">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('¿Estás completamente seguro de eliminar esta prenda permanentemente del catálogo de TEZAN? Se borrarán también sus variantes físicas de stock.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 px-2 py-1" title="Eliminar Prenda">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted py-4">No hay productos registrados en el inventario.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection