{{-- Tarjeta de la Matriz de Inventario. Recibe $variants (arreglo de [size_id, color_id, stock]) --}}
<div class="card border-0 shadow-sm rounded-0 bg-white p-4">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-uppercase tracking-wider small m-0">Matriz de Inventario</h5>
        <div>
            <button type="button" class="btn btn-sm btn-dark rounded-0 text-uppercase tracking-wider" id="add-variant-btn" style="font-size: 0.75rem;">
                <i class="bi bi-plus-lg me-1"></i> Añadir Variante
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 text-uppercase tracking-wider ms-1" data-bs-toggle="modal" data-bs-target="#addColorModal" style="font-size: 0.75rem;">
                <i class="bi bi-palette me-1"></i> Nuevo Color
            </button>
        </div>
    </div>

    <p class="text-muted small mb-3">Agrega las combinaciones físicas de stock disponibles para esta prenda. Cada combinación talla + color debe ser única.</p>

    <div id="variants-container">
        @foreach(array_values($variants) as $index => $variant)
            @include('admin.products.partials.variant-row', ['index' => $index, 'variant' => $variant])
        @endforeach
    </div>

    <template id="variant-row-template">
        @include('admin.products.partials.variant-row', ['index' => '__INDEX__', 'variant' => []])
    </template>
</div>
