@extends('layouts.admin')

@section('admin_content')
<div class="mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Registrar Nueva Prenda</h2>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger py-2 small rounded-0">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
                <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Detalles de la Prenda</h5>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-medium text-uppercase text-muted">Nombre del Producto</label>
                    <input type="text" class="form-control rounded-0 border-dark-subtle" id="name" name="name" value="{{ old('name') }}" maxlength="150" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label small fw-medium text-uppercase text-muted">Slug (URL)</label>
                    <input type="text" class="form-control rounded-0 border-dark-subtle bg-light" id="slug" value="{{ old('slug') }}" readonly>
                    <div class="form-text text-muted small">Se genera automáticamente a partir del nombre.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label small fw-medium text-uppercase text-muted">Categoría / Colección</label>
                        <select class="form-select rounded-0 border-dark-subtle" id="category_id" name="category_id" required>
                            <option value="">Seleccionar...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} @if($cat->audience !== 'ambos') ({{ strtolower(\App\Models\Category::AUDIENCES[$cat->audience]) }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="base_price" class="form-label small fw-medium text-uppercase text-muted">Precio Base (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control rounded-0 border-dark-subtle" id="base_price" name="base_price" value="{{ old('base_price') }}" required>
                    </div>
                </div>

                @include('admin.products.partials.gender-select')

                <div class="mb-3">
                    <label for="images" class="form-label small fw-medium text-uppercase text-muted">Galería de Imágenes (Múltiple)</label>
                    <input class="form-control rounded-0 border-dark-subtle small" type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                    <div class="form-text text-muted small">Puedes seleccionar varias imágenes a la vez. La primera será la portada. Formatos permitidos: JPG, PNG, WEBP (máx. 10 MB).</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-medium text-uppercase text-muted">Descripción o Fit de la Prenda</label>
                    <textarea class="form-control rounded-0 border-dark-subtle" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex gap-4 mt-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label small text-uppercase text-muted fw-medium" for="is_active">Visible en tienda</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label small text-uppercase text-muted fw-medium" for="is_featured">Prenda Destacada</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            @include('admin.products.partials.variants-card', ['variants' => old('variants', [[]])])
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-dark rounded-0 text-uppercase tracking-wider small px-5 py-2 fw-bold">Guardar Prenda</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-0 text-uppercase tracking-wider small px-4 py-2">Volver</a>
    </div>
</form>

@include('admin.products.partials.form-scripts')
@endsection
