@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->description ?? 'Prenda de diseño exclusivo con acabados de alta calidad por TEZAN.', 155))

@section('content')
<!-- Migas de pan -->
<nav aria-label="breadcrumb" class="small mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Inicio</a></li>
        @if($product->category)
            <li class="breadcrumb-item"><a href="{{ route('home', ['category' => $product->category->id]) }}#catalogo" class="text-muted text-decoration-none">{{ $product->category->name }}</a></li>
        @endif
        <li class="breadcrumb-item active text-dark" aria-current="page">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row g-5">
    <!-- Galería Multimedia Interactiva -->
    <div class="col-12 col-md-6">
        @if($product->images->isNotEmpty())
            <!-- Imagen Principal (las imágenes ya vienen ordenadas con la portada primero) -->
            <div class="mb-3 text-center bg-light p-2 border">
                <img id="main-product-image"
                     src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                     class="img-fluid"
                     style="max-height: 560px; width: 100%; object-fit: cover; transition: opacity 0.2s ease-in-out;"
                     alt="{{ $product->name }}">
            </div>

            <!-- Miniaturas Dinámicas (Hover / Clic) -->
            @if($product->images->count() > 1)
                <div class="d-flex gap-2 overflow-auto pb-2">
                    @foreach($product->images as $index => $img)
                        <button type="button" class="thumbnail-wrapper btn p-1 rounded-0 border {{ $index === 0 ? 'border-dark' : 'border-light-subtle' }}" aria-label="Ver imagen {{ $index + 1 }}">
                            <img src="{{ asset('storage/' . $img->image_path) }}"
                                 class="product-thumb"
                                 style="width: 70px; height: 70px; object-fit: cover; display: block;"
                                 data-large-src="{{ asset('storage/' . $img->image_path) }}"
                                 loading="lazy"
                                 alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        @else
            <div class="bg-light d-flex align-items-center justify-content-center border" style="height: 450px;">
                <span class="text-muted text-uppercase tracking-wider">Sin imagen disponible</span>
            </div>
        @endif
    </div>

    <!-- Detalles y Formulario de Selección -->
    <div class="col-12 col-md-6">
        <span class="text-uppercase small text-muted tracking-wider d-block mb-1">
            {{ $product->category->name ?? 'Colección TEZAN' }}
        </span>
        <h1 class="fw-bold text-uppercase tracking-wider mb-2 h2">{{ $product->name }}</h1>
        <p class="fw-bold text-dark mb-4 fs-3">S/ {{ number_format($product->base_price, 2) }}</p>

        <div class="border-top border-bottom py-3 mb-4">
            <p class="text-secondary mb-0" style="white-space: pre-line;">{{ $product->description ?? 'Prenda de diseño exclusivo con acabados de alta calidad por TEZAN.' }}</p>
        </div>

        @if($sizes->isEmpty())
            <div class="alert alert-light border rounded-0 small text-uppercase tracking-wider">Esta prenda no tiene stock disponible por el momento.</div>
        @else
            <!-- Formulario de Selección e Intención de Compra -->
            <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="buy_now" id="buy_now_input" value="0">

                <!-- Selector de Talla -->
                <div class="mb-3">
                    <label for="size-select" class="form-label text-uppercase small fw-bold text-dark">Talla</label>
                    <select id="size-select" name="size_id" class="form-select rounded-0 border-dark" required>
                        <option value="">Seleccionar Talla...</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Color (se llena según la talla elegida) -->
                <div class="mb-3">
                    <label for="color-select" class="form-label text-uppercase small fw-bold text-dark">Color</label>
                    <select id="color-select" name="color_id" class="form-select rounded-0 border-dark" required disabled>
                        <option value="">Primero elige una talla...</option>
                    </select>
                </div>

                <!-- Disponibilidad -->
                <div id="stock-status" class="mb-3 small d-none" aria-live="polite">
                    <span class="text-uppercase text-muted fw-medium">Disponibilidad:</span>
                    <span id="stock-badge" class="badge rounded-0 px-2 ms-1"></span>
                </div>

                <!-- Control de Cantidad -->
                <div class="mb-4">
                    <label for="qty-input" class="form-label text-uppercase small fw-bold text-dark">Cantidad</label>
                    <div class="input-group" style="max-width: 140px;">
                        <button class="btn btn-outline-dark rounded-0 px-3" type="button" id="btn-qty-minus" aria-label="Restar una unidad">-</button>
                        <input type="number" id="qty-input" name="quantity" class="form-control rounded-0 text-center border-dark" value="1" min="1" required>
                        <button class="btn btn-outline-dark rounded-0 px-3" type="button" id="btn-qty-plus" aria-label="Sumar una unidad">+</button>
                    </div>
                </div>

                <!-- Acciones Principales -->
                <div class="d-grid gap-2">
                    <!-- 1. Añadir al carrito (Abre el Offcanvas lateral) -->
                    <button type="submit" id="btn-add-cart" class="btn btn-outline-dark rounded-0 text-uppercase tracking-wider fw-bold py-3" disabled>
                        Agregar al carrito
                    </button>

                    <!-- 2. Comprar ahora (Redirección directa a Checkout) -->
                    <button type="submit" id="btn-buy-now" class="btn btn-dark rounded-0 text-uppercase tracking-wider fw-bold py-3" disabled>
                        Comprar ahora
                    </button>
                </div>
            </form>
        @endif

        <!-- Información de servicio -->
        <ul class="list-unstyled small text-muted mt-4 mb-0">
            <li class="mb-2"><i class="bi bi-truck me-2 text-dark"></i> Envío gratis en compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</li>
            <li class="mb-2"><i class="bi bi-shield-check me-2 text-dark"></i> Pago seguro con tarjeta o Yape</li>
            <li><i class="bi bi-arrow-repeat me-2 text-dark"></i> Cambios de talla sin complicaciones</li>
        </ul>
    </div>
</div>

<!-- Prendas Relacionadas -->
@if($relatedProducts->isNotEmpty())
    <section class="mt-5 pt-5 border-top">
        <h2 class="h6 fw-bold text-uppercase tracking-wider mb-4">También te puede gustar</h2>
        <div class="row row-cols-2 row-cols-md-4 g-4">
            @foreach($relatedProducts as $related)
                <div class="col">
                    <a href="{{ route('shop.product', $related->slug) }}" class="card h-100 border-0 rounded-0 text-decoration-none product-hover-card">
                        <div class="bg-light" style="height: 240px;">
                            @if($related->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $related->images->first()->image_path) }}" class="w-100 h-100 object-fit-cover" loading="lazy" alt="{{ $related->name }}">
                            @else
                                <div class="h-100 d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image fs-2"></i></div>
                            @endif
                        </div>
                        <div class="card-body px-0">
                            <h3 class="h6 text-dark mb-1" style="font-size: 0.9rem;">{{ $related->name }}</h3>
                            <span class="fw-bold text-dark small">S/ {{ number_format($related->base_price, 2) }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Matriz de variantes (talla, color, stock) enviada desde el servidor
        const variants = @json($variantMatrix);

        // 1. Intercambio Dinámico de Imagen Principal al pasar/hacer clic en miniaturas
        const mainImage = document.getElementById('main-product-image');
        const wrappers = document.querySelectorAll('.thumbnail-wrapper');

        wrappers.forEach((wrapper) => {
            const thumb = wrapper.querySelector('.product-thumb');

            function updateMainImage() {
                if (mainImage) {
                    mainImage.style.opacity = '0.4';
                    setTimeout(() => {
                        mainImage.src = thumb.getAttribute('data-large-src');
                        mainImage.style.opacity = '1';
                    }, 100);
                }
                wrappers.forEach(w => w.classList.replace('border-dark', 'border-light-subtle'));
                wrapper.classList.replace('border-light-subtle', 'border-dark');
            }

            wrapper.addEventListener('mouseenter', updateMainImage);
            wrapper.addEventListener('click', updateMainImage);
        });

        const form = document.getElementById('add-to-cart-form');
        if (!form) return;

        // 2. Filtrado de colores por talla y estado de stock
        const sizeSelect = document.getElementById('size-select');
        const colorSelect = document.getElementById('color-select');
        const stockStatus = document.getElementById('stock-status');
        const stockBadge = document.getElementById('stock-badge');
        const qtyInput = document.getElementById('qty-input');
        const btnAddCart = document.getElementById('btn-add-cart');
        const btnBuyNow = document.getElementById('btn-buy-now');
        let maxStock = 0;

        function setBuyable(enabled) {
            btnAddCart.disabled = !enabled;
            btnBuyNow.disabled = !enabled;
        }

        sizeSelect.addEventListener('change', function () {
            colorSelect.innerHTML = '<option value="">Seleccionar Color...</option>';
            stockStatus.classList.add('d-none');
            setBuyable(false);

            const options = variants.filter(v => String(v.size_id) === this.value);
            options.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.color_id;
                opt.textContent = v.stock > 0 ? v.color_name : `${v.color_name} (agotado)`;
                colorSelect.appendChild(opt);
            });
            colorSelect.disabled = options.length === 0;
        });

        colorSelect.addEventListener('change', function () {
            const match = variants.find(v => String(v.size_id) === sizeSelect.value && String(v.color_id) === this.value);
            if (!match) {
                stockStatus.classList.add('d-none');
                setBuyable(false);
                return;
            }

            maxStock = match.stock;
            stockStatus.classList.remove('d-none');

            if (match.stock > 0) {
                stockBadge.className = 'badge rounded-0 px-2 ms-1 bg-success-subtle text-success';
                stockBadge.textContent = match.stock <= 3 ? `¡Últimas ${match.stock} unidades!` : 'En stock';
                qtyInput.max = match.stock;
                if (parseInt(qtyInput.value) > match.stock) qtyInput.value = match.stock;
                setBuyable(true);
            } else {
                stockBadge.className = 'badge rounded-0 px-2 ms-1 bg-danger-subtle text-danger';
                stockBadge.textContent = 'Agotado temporalmente';
                setBuyable(false);
            }
        });

        // 3. Control de Cantidad (+ / -) limitado al stock disponible
        document.getElementById('btn-qty-minus').addEventListener('click', function () {
            const current = parseInt(qtyInput.value) || 1;
            if (current > 1) qtyInput.value = current - 1;
        });

        document.getElementById('btn-qty-plus').addEventListener('click', function () {
            const current = parseInt(qtyInput.value) || 1;
            if (!maxStock || current < maxStock) qtyInput.value = current + 1;
        });

        // 4. Procesamiento AJAX Unificado (submitter indica qué botón se presionó)
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const isBuyNow = e.submitter === btnBuyNow;
            document.getElementById('buy_now_input').value = isBuyNow ? '1' : '0';
            setBuyable(false);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            })
            .then(response => response.json().then(data => {
                if (!response.ok) throw data;
                return data;
            }))
            .then(data => {
                if (isBuyNow) {
                    // Comprar Ahora: Redirección inmediata a Checkout (Guest o Logueado)
                    window.location.href = "{{ route('checkout.index') }}";
                    return;
                }

                // Agregar al Carrito: Actualizar badge y abrir mini-carrito lateral
                const cartBadge = document.getElementById('cart-count-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                    cartBadge.classList.remove('d-none');
                }

                document.getElementById('mini-cart-body').innerHTML = data.html;
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('miniCartCanvas')).show();
            })
            .catch(error => {
                const firstValidationError = error.errors ? Object.values(error.errors)[0][0] : null;
                alert(error.error || firstValidationError || 'Ocurrió un error al procesar la solicitud.');
            })
            .finally(() => setBuyable(true));
        });
    });
</script>
@endpush
