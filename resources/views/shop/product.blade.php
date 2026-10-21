@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->description ?? 'Prenda TEZAN hecha en Perú.', 155))

@section('content')
@php
    $colors = $product->variants->pluck('color')->filter()->unique('id');
@endphp

<nav aria-label="Ruta" class="small mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="link-dark">Inicio</a></li>
        @if($product->category)
            <li class="breadcrumb-item"><a href="{{ route('home', ['category' => $product->category->id]) }}#catalogo" class="link-dark">{{ $product->category->name }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
    </ol>
</nav>

<div class="row g-4 g-lg-5">
    <!-- Galería -->
    <div class="col-12 col-lg-7">
        <div class="tz-gallery-main">
            @if($product->images->isNotEmpty())
                <img id="main-product-image" src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}">
            @else
                <div class="h-100 d-flex align-items-center justify-content-center text-muted">Sin foto por ahora</div>
            @endif
        </div>

        @if($product->images->count() > 1)
            <div class="tz-thumbs mt-1" role="group" aria-label="Fotos de la prenda">
                @foreach($product->images as $index => $img)
                    <button type="button" class="product-thumb" data-large-src="{{ asset('storage/' . $img->image_path) }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Ver foto {{ $index + 1 }}">
                        <img src="{{ asset('storage/' . $img->image_path) }}" loading="lazy" alt="">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Datos y compra -->
    <div class="col-12 col-lg-5">
        <h1 class="display-5 mb-2">{{ $product->name }}</h1>
        <p class="tz-price fs-3 mb-4">S/ {{ number_format($product->base_price, 2) }}</p>

        @if($product->description)
            <p class="mb-4" style="white-space: pre-line; max-width: 60ch;">{{ $product->description }}</p>
        @endif

        @if($sizes->isEmpty())
            <div class="tz-tile tz-ink">Esta prenda está agotada por ahora.</div>
        @else
            <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="buy_now" id="buy_now_input" value="0">

                <fieldset class="mb-4">
                    <legend class="fs-6 fw-semibold mb-2">Talla</legend>
                    <div class="tz-choices">
                        @foreach($sizes as $size)
                            <label class="tz-choice">
                                <input type="radio" name="size_id" value="{{ $size->id }}" required>
                                <span>{{ $size->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="mb-3">
                    <legend class="fs-6 fw-semibold mb-2">Color</legend>
                    <div class="tz-choices">
                        @foreach($colors as $color)
                            <label class="tz-choice">
                                <input type="radio" name="color_id" value="{{ $color->id }}" required>
                                <span><i class="tz-swatch" style="background: {{ $color->hex_code ?? 'transparent' }}"></i>{{ $color->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <p id="stock-status" class="small mb-4" aria-live="polite">Elige talla y color para ver la disponibilidad.</p>

                <div class="d-flex align-items-stretch gap-2 mb-3">
                    <label for="qty-input" class="visually-hidden">Cantidad</label>
                    <div class="input-group flex-nowrap" style="width: 140px;">
                        <button class="btn btn-outline-dark px-3" type="button" id="btn-qty-minus" aria-label="Quitar una unidad">−</button>
                        <input type="number" id="qty-input" name="quantity" class="form-control text-center" value="1" min="1" required>
                        <button class="btn btn-outline-dark px-3" type="button" id="btn-qty-plus" aria-label="Agregar una unidad">+</button>
                    </div>
                    <button type="submit" id="btn-add-cart" class="btn btn-dark flex-grow-1 py-3" disabled>Agregar a la bolsa</button>
                </div>
                <button type="submit" id="btn-buy-now" class="btn btn-outline-dark w-100 py-3" disabled>Comprar ahora</button>
            </form>
        @endif

        <ul class="list-unstyled mt-5 mb-0">
            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Hecho en Perú</li>
            <li class="mb-2"><i class="bi bi-truck me-2"></i>Envío gratis desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}; si no, S/ {{ number_format(\App\Support\Cart::SHIPPING_COST, 0) }}</li>
            <li><i class="bi bi-credit-card me-2"></i>Paga con tarjeta o Yape</li>
        </ul>
    </div>
</div>

@if($relatedProducts->isNotEmpty())
    <section class="mt-5 pt-4" aria-labelledby="relacionados">
        <h2 id="relacionados" class="h1 mb-4">También te puede gustar</h2>
        <div class="tz-grid">
            @foreach($relatedProducts as $related)
                @include('shop.partials.product-tile', ['product' => $related])
            @endforeach
        </div>
    </section>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const variants = @json($variantMatrix);

        // 1. Galería: la miniatura elegida pasa a la foto principal
        const mainImage = document.getElementById('main-product-image');
        const thumbs = document.querySelectorAll('.product-thumb');
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImage.style.opacity = '0.4';
                setTimeout(() => { mainImage.src = thumb.dataset.largeSrc; mainImage.style.opacity = '1'; }, 100);
                thumbs.forEach(t => t.setAttribute('aria-current', t === thumb ? 'true' : 'false'));
            });
        });

        const form = document.getElementById('add-to-cart-form');
        if (!form) return;

        // 2. Disponibilidad por talla y color
        const sizeInputs = form.querySelectorAll('input[name="size_id"]');
        const colorInputs = form.querySelectorAll('input[name="color_id"]');
        const status = document.getElementById('stock-status');
        const qtyInput = document.getElementById('qty-input');
        const btnAddCart = document.getElementById('btn-add-cart');
        const btnBuyNow = document.getElementById('btn-buy-now');
        let maxStock = 0;

        const checked = name => form.querySelector(`input[name="${name}"]:checked`)?.value;
        const setBuyable = ok => { btnAddCart.disabled = !ok; btnBuyNow.disabled = !ok; };

        function sync() {
            const size = checked('size_id');

            // Colores que no existen o están agotados en la talla elegida
            colorInputs.forEach(input => {
                const v = variants.find(v => String(v.size_id) === size && String(v.color_id) === input.value);
                input.disabled = Boolean(size) && (!v || v.stock < 1);
                if (input.disabled && input.checked) input.checked = false;
            });

            const color = checked('color_id');
            const match = variants.find(v => String(v.size_id) === size && String(v.color_id) === color);

            if (!size || !color) {
                status.textContent = size ? 'Ahora elige un color.' : 'Elige talla y color para ver la disponibilidad.';
                setBuyable(false);
                return;
            }

            maxStock = match.stock;
            qtyInput.max = maxStock;
            if (parseInt(qtyInput.value) > maxStock) qtyInput.value = maxStock;
            status.textContent = maxStock <= 3 ? `Quedan ${maxStock} en esta talla y color.` : 'Disponible.';
            setBuyable(true);
        }

        sizeInputs.forEach(i => i.addEventListener('change', sync));
        colorInputs.forEach(i => i.addEventListener('change', sync));

        // Si solo hay una opción posible, se elige sola
        if (sizeInputs.length === 1) sizeInputs[0].checked = true;
        sync();
        const enabledColors = [...colorInputs].filter(i => !i.disabled);
        if (enabledColors.length === 1) { enabledColors[0].checked = true; sync(); }

        // 3. Cantidad limitada al stock
        document.getElementById('btn-qty-minus').addEventListener('click', () => {
            const current = parseInt(qtyInput.value) || 1;
            if (current > 1) qtyInput.value = current - 1;
        });
        document.getElementById('btn-qty-plus').addEventListener('click', () => {
            const current = parseInt(qtyInput.value) || 1;
            if (!maxStock || current < maxStock) qtyInput.value = current + 1;
        });

        // 4. Envío por AJAX; "Comprar ahora" va directo al checkout
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
                    window.location.href = "{{ route('checkout.index') }}";
                    return;
                }
                document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.cart_count);
                document.getElementById('mini-cart-body').innerHTML = data.html;
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('miniCartCanvas')).show();
            })
            .catch(error => {
                const firstValidationError = error.errors ? Object.values(error.errors)[0][0] : null;
                status.textContent = error.error || firstValidationError || 'No se pudo agregar la prenda. Inténtalo de nuevo.';
            })
            .finally(sync);
        });
    });
</script>
@endpush
