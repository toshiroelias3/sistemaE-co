@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Categorías</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-dark rounded-0 text-uppercase small tracking-wider px-4">
        <i class="bi bi-plus-lg me-2"></i> Nueva Categoría
    </a>
</div>

<div class="card border-0 shadow-sm rounded-0 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light text-uppercase tracking-wider text-muted" style="font-size: 0.75rem;">
                    <tr>
                        <th class="py-3">ID</th>
                        <th class="py-3 text-start">Nombre</th>
                        <th class="py-3 text-start">Slug (URL)</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3">Sección</th>
                        <th class="py-3">Prendas</th>
                        <th class="py-3">Estado</th>
                        <th class="py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td class="fw-bold">#{{ $cat->id }}</td>
                            <td class="text-start fw-medium">{{ $cat->name }}</td>
                            <td class="text-start text-muted">{{ $cat->slug }}</td>
                            <td>
                                @if($cat->parent)
                                    <span class="badge bg-light text-dark border rounded-0 text-uppercase" style="font-size: 0.65rem;">
                                        Subcategoría de: {{ $cat->parent->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-0 text-uppercase" style="font-size: 0.65rem;">
                                        Categoría Principal
                                    </span>
                                @endif
                            </td>
                            <td>{{ \App\Models\Category::AUDIENCES[$cat->audience] ?? $cat->audience }}</td>
                            <td class="fw-bold">{{ $cat->products_count }}</td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge bg-success-subtle text-success rounded-0 px-2 text-uppercase" style="font-size: 0.65rem;">Activo</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-0 px-2 text-uppercase" style="font-size: 0.65rem;">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría permanentemente de TEZAN?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 px-2 py-1" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted py-4">No hay categorías registradas en el sistema.</td>
                        </tr>
                    @endforelse 
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $categories->links() }}
</div>
@endsection