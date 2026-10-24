{{-- Fila de la tabla de stock: un color con una celda por talla.
     $colorId/$colorName/$colorHex pueden ser marcadores (__COLOR_ID__...) para la plantilla JS.
     $cells es [size_id => stock]; una celda vacía significa que esa combinación no se vende. --}}
<tr data-color-id="{{ $colorId }}">
    <th scope="row" class="fw-normal text-nowrap small">
        <span class="d-inline-block border me-2 align-middle color-swatch" style="width: 14px; height: 14px; background: {{ $colorHex ?: 'transparent' }};"></span>
        <span class="color-name">{{ $colorName }}</span>
    </th>
    @foreach($sizes as $size)
        <td>
            <input type="number" min="0" step="1"
                   name="stock[{{ $colorId }}][{{ $size->id }}]"
                   value="{{ $cells[$size->id] ?? '' }}"
                   class="form-control form-control-sm rounded-0 text-center px-1 stock-cell" style="min-width: 46px;"
                   placeholder="—"
                   aria-label="Stock talla {{ $size->name }}">
        </td>
    @endforeach
    <td class="text-end text-nowrap ps-1">
        <button type="button" class="btn btn-sm btn-link link-secondary p-0 me-1 fill-row-btn" title="Llenar las celdas vacías de este color">
            <i class="bi bi-arrow-right-square"></i>
        </button>
        <button type="button" class="btn btn-sm btn-link link-danger p-0 remove-color-btn" title="Quitar este color">
            <i class="bi bi-x-lg"></i>
        </button>
    </td>
</tr>
