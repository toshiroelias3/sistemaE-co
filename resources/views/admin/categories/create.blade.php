@extends('layouts.admin')

@section('admin_content')
<div class="mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Crear Categoría</h2>
</div>

<div class="card border-0 shadow-sm rounded-0 bg-white p-4" style="max-width: 600px;">
    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label small fw-medium text-uppercase text-muted">Nombre de la Categoría / Colección</label>
            <input type="text" class="form-control rounded-0 border-dark-subtle" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label small fw-medium text-uppercase text-muted">Slug (URL Amigable)</label>
            <input type="text" class="form-control rounded-0 border-dark-subtle bg-light" id="slug" name="slug" value="{{ old('slug') }}" readonly required>
            <div class="form-text text-muted small">Se genera automáticamente para optimizar SEO.</div>
        </div>

        <div class="mb-3">
            <label for="parent_id" class="form-label small fw-medium text-uppercase text-muted">Categoría Padre (Opcional)</label>
            <select class="form-select rounded-0 border-dark-subtle" id="parent_id" name="parent_id">
                <option value="">Ninguna (Definir como Principal)</option>
                @foreach($parentCategories as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>

        @include('admin.categories.partials.audience-select')

        <div class="mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-input-label small text-uppercase fw-medium text-muted" for="is_active">Categoría Activa</label>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark rounded-0 text-uppercase tracking-wider small px-4 py-2 fw-bold">Guardar Categoría</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-0 text-uppercase tracking-wider small px-4 py-2">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Usamos Javascript Nativo para evitar conflictos con los módulos de Vite
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('keyup', function() {
                let text = this.value;
                
                // Procesamiento limpio de slugs (remueve acentos y caracteres raros)
                let slug = text.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '') // Quita tildes
                    .replace(/[^a-z0-9 -]/g, '')     // Quita símbolos
                    .replace(/\s+/g, '-')            // Espacios por guiones
                    .replace(/-+/g, '-');            // Evita doble guión
                
                slugInput.value = slug;
            });
        }
    }); // Cierre del DOMContentLoaded corregido
</script>
@endpush