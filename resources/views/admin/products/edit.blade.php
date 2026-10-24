@extends('layouts.admin')

@section('admin_content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Editar Prenda: {{ $product->name }}</h2>
    @if($product->is_active)
        <a href="{{ route('shop.product', $product->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-0 text-uppercase tracking-wider px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Ver en tienda
        </a>
    @endif
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger py-2 small rounded-0 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Columna Izquierda: Datos del Producto y Galería -->
        <div class="col-12 col-lg-7">
            <!-- Detalles base -->
            <div class="card border-0 shadow-sm rounded-0 bg-white p-4 mb-4">
                <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Detalles de la Prenda</h5>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-medium text-uppercase text-muted">Nombre del Producto</label>
                    <input type="text" class="form-control rounded-0 border-dark-subtle" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label small fw-medium text-uppercase text-muted">Slug (URL)</label>
                    <input type="text" class="form-control rounded-0 border-dark-subtle bg-light" id="slug" value="{{ old('slug', $product->slug) }}" readonly>
                    <div class="form-text text-muted small">Se regenera a partir del nombre al guardar.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label small fw-medium text-uppercase text-muted">Categoría / Colección</label>
                        <select class="form-select rounded-0 border-dark-subtle" id="category_id" name="category_id" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} @if($cat->audience !== 'ambos') ({{ strtolower(\App\Models\Category::AUDIENCES[$cat->audience]) }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="base_price" class="form-label small fw-medium text-uppercase text-muted">Precio Base (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control rounded-0 border-dark-subtle" id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" required>
                    </div>
                </div>

                @include('admin.products.partials.gender-select', ['current' => $product->gender])

                <div class="mb-3">
                    <label for="description" class="form-label small fw-medium text-uppercase text-muted">Descripción o Fit</label>
                    <textarea class="form-control rounded-0 border-dark-subtle" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="d-flex gap-4 mt-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label small text-uppercase text-muted fw-medium" for="is_active">Visible en tienda</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label small text-uppercase text-muted fw-medium" for="is_featured">Prenda Destacada</label>
                    </div>
                </div>
            </div>

            <!-- Galería Multimedia e Imágenes Actuales -->
            <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
                <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Galería Multimedia</h5>

                <div class="mb-4">
                    <label for="images" class="form-label small fw-medium text-uppercase text-muted">Subir Nuevas Imágenes (Opcional)</label>
                    <input type="file" class="form-control rounded-0 border-dark-subtle" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                    <div class="form-text small text-muted">Nota: Al subir imágenes nuevas, estas se añadirán a la galería del producto.</div>
                </div>

                <span class="form-label small fw-medium text-uppercase text-muted d-block mb-2">Imágenes Actuales:</span>
                <div class="row g-3">
                    @forelse($product->images as $img)
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="card rounded-0 border p-2 text-center bg-light position-relative">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="object-fit-cover w-100 mb-2" style="height: 110px;" alt="Imagen de {{ $product->name }}">

                                <div class="d-flex flex-column gap-1">
                                    @if($img->is_primary)
                                        <span class="badge bg-dark text-uppercase w-100 py-1 rounded-0" style="font-size: 0.65rem;">
                                            <i class="bi bi-star-fill me-1"></i> Portada
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-outline-dark btn-sm rounded-0 w-100 image-action-btn"
                                                data-action="{{ route('admin.products.images.setPrimary', [$product->id, $img->id]) }}" data-method="POST"
                                                style="font-size: 0.65rem;">
                                            Definir Portada
                                        </button>
                                    @endif

                                    <!-- Botón para eliminar foto individual -->
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-0 w-100 image-action-btn"
                                            data-action="{{ route('admin.products.images.destroy', $img->id) }}" data-method="DELETE"
                                            data-confirm="¿Seguro que deseas eliminar esta foto de la galería?"
                                            style="font-size: 0.65rem;">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <span class="text-muted small">Esta prenda no tiene imágenes asignadas en el servidor.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Matriz de Inventario y Variantes -->
        <div class="col-12 col-lg-5">
            @include('admin.products.partials.variants-card', [
                'variants' => old('variants', $product->variants->map->only(['size_id', 'color_id', 'stock'])->all()) ?: [[]],
            ])
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-dark rounded-0 text-uppercase tracking-wider small px-5 py-2 fw-bold">Actualizar Prenda</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-0 text-uppercase tracking-wider small px-4 py-2">Volver</a>
    </div>
</form>

<!-- Formulario Oculto Auxiliar para Acciones POST/DELETE de Imágenes -->
<form id="aux-image-form" action="" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="_method" id="aux-form-method" value="POST">
</form>

@include('admin.products.partials.form-scripts')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Definir portada / eliminar imagen mediante el formulario auxiliar
        const form = document.getElementById('aux-image-form');
        const methodInput = document.getElementById('aux-form-method');

        document.querySelectorAll('.image-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.dataset.confirm && !confirm(this.dataset.confirm)) return;

                methodInput.value = this.dataset.method;
                form.action = this.dataset.action;
                form.submit();
            });
        });
    });
</script>
@endpush
