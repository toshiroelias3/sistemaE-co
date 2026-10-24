{{-- Para quién es la prenda. Una unisex aparece en Varones y en Mujeres. --}}
<div class="mb-3">
    <label for="gender" class="form-label small fw-medium text-uppercase text-muted">Para</label>
    <select class="form-select rounded-0 border-dark-subtle" id="gender" name="gender" required>
        @foreach(\App\Models\Product::GENDERS as $value => $label)
            <option value="{{ $value }}" {{ old('gender', $current ?? 'unisex') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="form-text text-muted small">Unisex aparece en las secciones Varones y Mujeres de la tienda.</div>
</div>
