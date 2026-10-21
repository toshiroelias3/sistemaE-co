@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->description ?? 'Prenda TEZAN hecha en Perú.', 155))

@section('full')
@php
    $colors = $product->variants->pluck('color')->filter()->unique('id');
@endphp

<div class="container-fluid px-0 px-lg-5 pt-lg-4">
    <div class="row g-0 g-lg-5">
        <!-- Fotos: deslizables en celular, en dos columnas en escritorio -->
        <div class="col-12 col-lg-7 col-xl-8">
            <div class="tz-gallery" aria-label="Fotos de {{ $product->name }}">
                @forelse($product->images as $index => $img)
                    <figure>
                        <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $index === 0 ? $product->name : '' }}" {{ $index > 1 ? 'loading=lazy' : '' }}>
                    </figure>
                @empty
                    <figure class="d-flex align-items-center justify-content-center text-muted">Foto próximamente</figure>
                @endforelse
            </div>
            @if($product->images->count() > 1)
                <p class="text-center small text-muted d-lg-none mt-2 mb-0">Desliza para ver más fotos</p>
            @endif
        </div>

        <!-- Panel de compra -->
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="tz-buy-panel px-3 px-lg-0 py-4 py-lg-0">
                <nav aria-label="Ruta" class="small mb-3">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="link-secondary">Inicio</a></li>
                        @if($product->category)
                            <li class="breadcrumb-item"><a href="{{ route('home', ['category' => $product->category->id]) }}#catalogo" class="link-secondary">{{ $product->category->name }}</a></li>
                        @endif
                    </ol>
                </nav>

                <h1 class="tz-label fs-6 mb-2" style="letter-spacing: 0.06em;">{{ $product->name }}</h1>
                <p class="tz-price fs-5 mb-4">S/ {{ number_format($product->base_price, 2) }}</p>

                @if($sizes->isEmpty())
                    <p class="border p-3 mb-4">Esta prenda está agotada por ahora.</p>
                @else
                    <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="buy_now" id="buy_now_input" value="0">

                        <fieldset class="mb-4">
                            <legend class="tz-label mb-2">Color: <span id="color-name" class="text-muted"></span></legend>
                            <div class="tz-choices">
                                @foreach($colors as $color)
                                    <label class="tz-choice">
                                        <input type="radio" name="color_id" value="{{ $color->id }}" data-name="{{ $color->name }}" required>
                                        <span><i class="tz-swatch" style="background: {{ $color->hex_code ?? 'transparent' }}"></i>{{ $color->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="mb-3">
                            <legend class="tz-label mb-2">Talla</legend>
                            <div class="tz-choices">
                                @foreach($sizes as $size)
                                    <label class="tz-choice">
                                        <input type="radio" name="size_id" value="{{ $size->id }}" required>
                                        <span>{{ $size->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <p id="stock-status" class="small text-muted mb-4" aria-live="polite">Elige color y talla.</p>

                        <input type="hidden" id="qty-input" name="quantity" value="1">
                        <button type="submit" id="btn-add-cart" class="btn btn-dark w-100 mb-2" disabled>Añadir a la bolsa</button>
                        <button type="submit" id="btn-buy-now" class="btn btn-outline-dark w-100" disabled>Comprar ahora</button>
                    </form>
                @endif

                <div class="mt-4">
                    @if($product->description)
                        <details class="tz-details" open>
                            <summary class="tz-label">Descripción</summary>
                            <p class="small pb-3 mb-0" style="white-space: pre-line;">{{ $product->description }}</p>
                        </details>
                    @endif
                    <details class="tz-details">
                        <summary class="tz-label">Envío</summary>
                        <p class="small pb-3 mb-0">Despachamos a todo el Perú. Envío gratis en compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}; si no, cuesta S/ {{ number_format(\App\Support\Cart::SHIPPING_COST, 0) }}.</p>
                    </details>
                    <details class="tz-details border-bottom">
                        <summary class="tz-label">Pago</summary>
                        <p class="small pb-3 mb-0">Paga con tarjeta de crédito, débito o Yape.</p>
                    </details>
                </div>
            </div>
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="px-3 px-lg-0 py-5 mt-lg-4" aria-labelledby="relacionados">
            <h2 id="relacionados" class="tz-label mb-4">También te puede gustar</h2>
            <div class="tz-grid">
                @foreach($relatedProducts as $related)
                    @include('shop.partials.product-tile', ['product' => $related])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const variants = @json($variantMatrix);
        const form = document.getElementById('add-to-cart-form');
        if (!form) return;

        const sizeInputs = form.querySelectorAll('input[name="size_id"]');
        const colorInputs = form.querySelectorAll('input[name="color_id"]');
        const status = document.getElementById('stock-status');
        const colorName = document.getElementById('color-name');
        const btnAddCart = document.getElementById('btn-add-cart');
        const btnBuyNow = document.getElementById('btn-buy-now');

        const checked = name => form.querySelector(`input[name="${name}"]:checked`);
        const setBuyable = ok => { btnAddCart.disabled = !ok; btnBuyNow.disabled = !ok; };

        // Tallas sin stock para el color elegido quedan tachadas
        function sync() {
            const color = checked('color_id');
            colorName.textContent = color ? color.dataset.name : '';

            sizeInputs.forEach(input => {
                const v = variants.find(v => String(v.color_id) === color?.value && String(v.size_id) === input.value);
                input.disabled = Boolean(color) && (!v || v.stock < 1);
                if (input.disabled && input.checked) input.checked = false;
            });

            const size = checked('size_id');
            if (!color || !size) {
                status.textContent = color ? 'Ahora elige tu talla.' : 'Elige color y talla.';
                setBuyable(false);
                return;
            }

            const match = variants.find(v => String(v.color_id) === color.value && String(v.size_id) === size.value);
            status.textContent = match.stock <= 3 ? `Últimas ${match.stock} unidades.` : 'Disponible.';
            setBuyable(true);
        }

        sizeInputs.forEach(i => i.addEventListener('change', sync));
        colorInputs.forEach(i => i.addEventListener('change', sync));

        // Con una sola opción posible, se elige sola
        if (colorInputs.length === 1) colorInputs[0].checked = true;
        sync();
        const enabledSizes = [...sizeInputs].filter(i => !i.disabled);
        if (enabledSizes.length === 1) { enabledSizes[0].checked = true; sync(); }

        // Envío por AJAX; "Comprar ahora" va directo al checkout
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
                status.textContent = error.error || firstValidationError || 'No se pudo añadir la prenda. Inténtalo de nuevo.';
            })
            .finally(sync);
        });
    });
</script>
@endpush
