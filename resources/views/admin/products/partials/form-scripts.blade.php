{{-- Modal de color y scripts compartidos por los formularios de creación y edición de prendas --}}
<div class="modal fade" id="addColorModal" tabindex="-1" aria-labelledby="addColorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-0 border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title fw-bold text-uppercase tracking-wider" id="addColorModalLabel">Registrar Nuevo Color para TEZAN</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-2 small rounded-0 d-none" id="modal-error-alert"></div>

                <div class="row g-2 mb-3">
                    <div class="col-9">
                        <label for="modal-color-name" class="form-label small fw-medium text-uppercase text-muted">Nombre del Color</label>
                        <input type="text" class="form-control rounded-0 border-dark-subtle" id="modal-color-name" placeholder="Ej: Verde Oliva, Off-White">
                    </div>
                    <div class="col-3">
                        <label for="modal-color-hex" class="form-label small fw-medium text-uppercase text-muted">Tono</label>
                        <input type="color" class="form-control form-control-color rounded-0 border-dark-subtle w-100" id="modal-color-hex" value="#1a1a1a">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-0 text-uppercase small tracking-wider" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark rounded-0 text-uppercase small tracking-wider" id="save-color-ajax-btn">Guardar Color</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Generador automático de Slugs (vista previa; el servidor genera el definitivo)
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                slugInput.value = this.value.toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9 -]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-');
            });
        }

        // 2. Tabla de stock (color × talla)
        const matrixBody = document.getElementById('matrix-body');
        const rowTemplate = document.getElementById('matrix-row-template');
        const addColorSelect = document.getElementById('add-color-select');
        const quickFill = document.getElementById('quick-fill-value');

        function refreshTotals() {
            const cells = [...matrixBody.querySelectorAll('.stock-cell')].filter(c => c.value !== '');
            document.getElementById('matrix-total').textContent = cells.reduce((sum, c) => sum + (parseInt(c.value) || 0), 0);
            document.getElementById('matrix-combos').textContent = cells.length;
            document.getElementById('matrix-empty').hidden = matrixBody.children.length > 0;
        }

        function addColorRow(id, name, hex) {
            if (matrixBody.querySelector(`tr[data-color-id="${id}"]`)) return;
            matrixBody.insertAdjacentHTML('beforeend', rowTemplate.innerHTML.replace(/__COLOR_ID__/g, id));
            const row = matrixBody.lastElementChild;
            row.querySelector('.color-name').textContent = name;      // textContent: el nombre nunca se interpreta como HTML
            row.querySelector('.color-swatch').style.background = hex || 'transparent';
            const option = addColorSelect.querySelector(`option[value="${id}"]`);
            if (option) option.disabled = true;
            addColorSelect.value = '';
            row.querySelector('.stock-cell').focus();
            refreshTotals();
        }

        if (matrixBody) {
            document.getElementById('add-color-btn').addEventListener('click', () => {
                const option = addColorSelect.selectedOptions[0];
                if (option && option.value) addColorRow(option.value, option.textContent.trim(), option.dataset.hex);
            });

            const fillEmpty = cells => cells.forEach(c => { if (c.value === '') c.value = quickFill.value || 0; });

            document.getElementById('fill-all-btn').addEventListener('click', () => {
                fillEmpty(matrixBody.querySelectorAll('.stock-cell'));
                refreshTotals();
            });

            matrixBody.addEventListener('click', e => {
                const row = e.target.closest('tr');
                if (e.target.closest('.fill-row-btn')) {
                    fillEmpty(row.querySelectorAll('.stock-cell'));
                } else if (e.target.closest('.remove-color-btn')) {
                    const option = addColorSelect.querySelector(`option[value="${row.dataset.colorId}"]`);
                    if (option) option.disabled = false;
                    row.remove();
                }
                refreshTotals();
            });

            matrixBody.addEventListener('input', refreshTotals);
            refreshTotals();
        }

        // 3. Guardar color vía AJAX y sumarlo a la tabla de stock
        const saveColorBtn = document.getElementById('save-color-ajax-btn');
        const colorInput = document.getElementById('modal-color-name');
        const colorHexInput = document.getElementById('modal-color-hex');
        const errorAlert = document.getElementById('modal-error-alert');

        if (saveColorBtn) {
            saveColorBtn.addEventListener('click', function() {
                const colorName = colorInput.value.trim();
                errorAlert.classList.add('d-none');

                if (!colorName) {
                    errorAlert.textContent = "Por favor, escribe el nombre del color.";
                    errorAlert.classList.remove('d-none');
                    return;
                }

                saveColorBtn.disabled = true;

                fetch("{{ route('admin.colors.ajax.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name: colorName, hex_code: colorHexInput.value })
                })
                .then(response => response.json().then(data => {
                    if (!response.ok) throw data;
                    return data;
                }))
                .then(data => {
                    // El color nuevo queda disponible y se agrega como fila de la tabla
                    addColorSelect.insertAdjacentHTML('beforeend', `<option value="${data.id}"></option>`);
                    addColorSelect.lastElementChild.textContent = data.name;
                    addColorSelect.lastElementChild.dataset.hex = colorHexInput.value;
                    addColorRow(data.id, data.name, colorHexInput.value);

                    colorInput.value = "";
                    bootstrap.Modal.getInstance(document.getElementById('addColorModal')).hide();
                })
                .catch(error => {
                    errorAlert.textContent = (error.errors && error.errors.name) ? error.errors.name[0] : (error.message || "Error al procesar el color.");
                    errorAlert.classList.remove('d-none');
                })
                .finally(() => saveColorBtn.disabled = false);
            });
        }
    });
</script>
@endpush
