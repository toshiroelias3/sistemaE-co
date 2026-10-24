{{-- Stock por color y talla en una sola tabla. Recibe $variants (arreglo de [size_id, color_id, stock]),
     $sizes y $colors. Si el formulario volvió con errores, se reconstruye desde old('stock'). --}}
@php
    $matrix = old('stock') ?? collect($variants)
        ->filter(fn ($v) => !empty($v['color_id']) && !empty($v['size_id']))
        ->groupBy('color_id')
        ->map(fn ($group) => $group->pluck('stock', 'size_id')->all())
        ->all();
    $colorsById = $colors->keyBy('id');
    $usedColorIds = array_map('intval', array_keys($matrix));
@endphp

<div class="card border-0 shadow-sm rounded-0 bg-white p-4">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-uppercase tracking-wider small m-0">Stock por color y talla</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 text-uppercase tracking-wider" data-bs-toggle="modal" data-bs-target="#addColorModal" style="font-size: 0.75rem;">
            <i class="bi bi-palette me-1"></i> Nuevo color
        </button>
    </div>

    <p class="text-muted small mb-3">
        Escribe cuántas unidades hay de cada talla en cada color. Deja la celda <strong>vacía</strong> si esa combinación no existe; pon <strong>0</strong> si existe pero está agotada.
    </p>

    @error('variants')
        <div class="alert alert-danger py-2 small rounded-0">{{ $message }}</div>
    @enderror

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-2" id="stock-matrix">
            <thead class="table-light small text-muted">
                <tr>
                    <th scope="col">Color</th>
                    @foreach($sizes as $size)
                        <th scope="col" class="text-center">{{ $size->name }}</th>
                    @endforeach
                    <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
            </thead>
            <tbody id="matrix-body">
                @foreach($matrix as $colorId => $cells)
                    @php $color = $colorsById->get((int) $colorId); @endphp
                    @if($color)
                        @include('admin.products.partials.matrix-row', [
                            'colorId' => $color->id,
                            'colorName' => $color->name,
                            'colorHex' => $color->hex_code,
                            'cells' => $cells,
                        ])
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="small text-muted mb-3" id="matrix-empty" @if(count($usedColorIds)) hidden @endif>Agrega al menos un color para cargar stock.</p>

    <!-- Agregar color y llenado rápido -->
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-7">
            <label for="add-color-select" class="form-label small text-muted mb-1">Agregar color</label>
            <div class="input-group input-group-sm">
                <select id="add-color-select" class="form-select rounded-0 border-dark-subtle">
                    <option value="">Elige un color…</option>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}" data-hex="{{ $color->hex_code }}" @disabled(in_array($color->id, $usedColorIds))>{{ $color->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-dark rounded-0" id="add-color-btn"><i class="bi bi-plus-lg"></i> Agregar</button>
            </div>
        </div>
        <div class="col-12 col-md-5">
            <label for="quick-fill-value" class="form-label small text-muted mb-1">Llenar celdas vacías con</label>
            <div class="input-group input-group-sm">
                <input type="number" min="0" id="quick-fill-value" class="form-control rounded-0 border-dark-subtle text-center" value="10">
                <button type="button" class="btn btn-outline-dark rounded-0" id="fill-all-btn">Todas</button>
            </div>
        </div>
    </div>

    <p class="small mt-3 mb-0">Total: <strong id="matrix-total">0</strong> unidades en <strong id="matrix-combos">0</strong> combinaciones.</p>

    <template id="matrix-row-template">
        @include('admin.products.partials.matrix-row', [
            'colorId' => '__COLOR_ID__',
            'colorName' => '',
            'colorHex' => '',
            'cells' => [],
        ])
    </template>
</div>
