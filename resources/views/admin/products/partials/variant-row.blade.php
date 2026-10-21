{{-- Fila de la matriz de inventario. $index puede ser un número o '__INDEX__' para la plantilla JS --}}
<div class="row g-2 align-items-end variant-row mb-3">
    <div class="col-5">
        <label class="form-label small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Talla</label>
        <select name="variants[{{ $index }}][size_id]" class="form-select form-select-sm rounded-0 border-dark-subtle" required>
            @foreach($sizes as $size)
                <option value="{{ $size->id }}" {{ ($variant['size_id'] ?? null) == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-5">
        <label class="form-label small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Color</label>
        <select name="variants[{{ $index }}][color_id]" class="form-select form-select-sm rounded-0 border-dark-subtle" required>
            @foreach($colors as $color)
                <option value="{{ $color->id }}" {{ ($variant['color_id'] ?? null) == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label class="form-label small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Stock</label>
        <input type="number" min="0" name="variants[{{ $index }}][stock]" class="form-control form-control-sm rounded-0 border-dark-subtle text-center" value="{{ $variant['stock'] ?? 10 }}" required>
    </div>
    <div class="col-12 mt-1 text-end">
        <button type="button" class="btn btn-xs text-danger py-0 px-1 remove-row-btn" style="font-size: 0.75rem;"><i class="bi bi-x-circle-fill"></i> Quitar variante</button>
    </div>
</div>
