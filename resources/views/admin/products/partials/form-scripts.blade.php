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

        // 2. Lógica de la Matriz Dinámica de Inventario
        const container = document.getElementById('variants-container');
        const addBtn = document.getElementById('add-variant-btn');
        const template = document.getElementById('variant-row-template');
        let variantIndex = container ? container.querySelectorAll('.variant-row').length : 0;

        if (addBtn && container && template) {
            addBtn.addEventListener('click', function() {
                const html = template.innerHTML.replace(/__INDEX__/g, variantIndex++);
                container.insertAdjacentHTML('beforeend', html);
            });
        }

        if (container) {
            container.addEventListener('click', function(e) {
                if (!e.target.closest('.remove-row-btn')) return;

                if (container.querySelectorAll('.variant-row').length > 1) {
                    e.target.closest('.variant-row').remove();
                } else {
                    alert('El producto debe mantener al menos una variante activa de inventario.');
                }
            });
        }

        // 3. Guardar color vía AJAX y agregarlo a todos los selectores (incluida la plantilla)
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
                    const option = `<option value="${data.id}"></option>`;
                    document.querySelectorAll('select[name*="[color_id]"]').forEach(select => {
                        select.insertAdjacentHTML('beforeend', option);
                        select.lastElementChild.textContent = data.name;
                    });
                    if (template) {
                        const tplSelect = template.content.querySelector('select[name*="[color_id]"]');
                        tplSelect.insertAdjacentHTML('beforeend', option);
                        tplSelect.lastElementChild.textContent = data.name;
                    }

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
