{{-- En qué sección de la tienda aparece la categoría --}}
<div class="mb-3">
    <label for="audience" class="form-label small fw-medium text-uppercase text-muted">Sección</label>
    <select class="form-select rounded-0 border-dark-subtle" id="audience" name="audience" required>
        @foreach(\App\Models\Category::AUDIENCES as $value => $label)
            <option value="{{ $value }}" {{ old('audience', $current ?? 'ambos') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="form-text text-muted small">Define si la categoría aparece en Varones, en Mujeres o en ambas.</div>
</div>
