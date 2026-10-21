@extends('layouts.app')

@section('title', 'Básicos de algodón hechos en Perú')

@section('full')
@php
    $shortcutCategories = $categories->take(3);
    $featuredSizes = $featured ? $featured->variants->pluck('size')->filter()->unique('id')->sortBy('id') : collect();
    $featuredColors = $featured ? $featured->variants->pluck('color')->filter()->unique('id') : collect();
@endphp

<!-- Primer viewport: el piso de damero -->
<section class="tz-floor-wrap" aria-label="TEZAN">
    <div class="tz-floor tz-hero">
        <h1 class="tz-tile tz-ink tz-word" style="--d: 0">TEZAN</h1>

        <div class="tz-tile tz-photo" style="--d: 4">
            @if($featured && $featured->images->isNotEmpty())
                <img src="{{ asset('storage/' . $featured->images->first()->image_path) }}" alt="{{ $featured->name }}" fetchpriority="high">
            @endif
        </div>

        <p class="tz-tile tz-copy m-0" style="--d: 2">Básicos de algodón hechos en Perú. Buena tela, buen fit y un precio justo.</p>

        @if($featured)
            <form class="tz-tile tz-ink tz-buy" style="--d: 5" action="{{ route('cart.add') }}" method="POST" id="hero-buy"
                  data-variants='@json($featured->variants->map(fn ($v) => [$v->size_id, $v->color_id])->values())'>
                @csrf
                <input type="hidden" name="product_id" value="{{ $featured->id }}">
                <input type="hidden" name="quantity" value="1">

                <div class="d-flex justify-content-between align-items-baseline gap-3">
                    <a href="{{ route('shop.product', $featured->slug) }}" class="tz-serif fs-4 text-decoration-none">{{ $featured->name }}</a>
                    <span class="tz-price fs-5">S/ {{ number_format($featured->base_price, 2) }}</span>
                </div>

                <fieldset>
                    <legend class="visually-hidden">Talla</legend>
                    <div class="tz-choices">
                        @foreach($featuredSizes as $size)
                            <label class="tz-choice">
                                <input type="radio" name="size_id" value="{{ $size->id }}" required {{ $loop->first ? 'checked' : '' }}>
                                <span>{{ $size->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="visually-hidden">Color</legend>
                    <div class="tz-choices">
                        @foreach($featuredColors as $color)
                            <label class="tz-choice">
                                <input type="radio" name="color_id" value="{{ $color->id }}" required {{ $loop->first ? 'checked' : '' }}>
                                <span><i class="tz-swatch" style="background: {{ $color->hex_code ?? 'transparent' }}"></i>{{ $color->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-light py-2">Agregar a la bolsa</button>
            </form>
        @else
            <a class="tz-tile tz-ink tz-buy" style="--d: 5" href="#catalogo">
                <span class="tz-serif fs-4">La nueva colección llega pronto</span>
                <span>Mira el catálogo <i class="bi bi-arrow-down"></i></span>
            </a>
        @endif

        <!-- Hechos de la marca -->
        <div class="tz-tile tz-tile-fact" style="grid-area: f1; --d: 8"><i class="bi bi-geo-alt"></i>Hecho en Perú</div>
        <div class="tz-tile tz-ink tz-tile-fact" style="grid-area: f2; --d: 9"><i class="bi bi-truck"></i>Despacho a todo el Perú</div>
        <div class="tz-tile tz-tile-fact" style="grid-area: f3; --d: 10"><i class="bi bi-credit-card"></i>Tarjeta o Yape</div>
        <div class="tz-tile tz-ink tz-tile-fact" style="grid-area: f4; --d: 11"><i class="bi bi-box-seam"></i>Envío gratis desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</div>

        <!-- Accesos al catálogo -->
        <a class="tz-tile" style="grid-area: l1; --d: 4" href="#catalogo">
            <i class="bi bi-arrow-down fs-5"></i><span class="fw-semibold">Ver todo</span>
        </a>
        @foreach(['l2', 'l3', 'l4'] as $i => $area)
            @if($cat = $shortcutCategories->get($i))
                <a class="tz-tile {{ $i % 2 === 0 ? 'tz-ink' : '' }}" style="grid-area: {{ $area }}; --d: {{ 5 + $i }}" href="{{ route('home', ['category' => $cat->id]) }}#catalogo">
                    <i class="bi bi-arrow-right fs-5"></i><span class="fw-semibold">{{ $cat->name }}</span>
                </a>
            @else
                <div class="tz-tile {{ $i % 2 === 0 ? 'tz-ink' : '' }}" style="grid-area: {{ $area }}; --d: {{ 5 + $i }}" aria-hidden="true"></div>
            @endif
        @endforeach
    </div>
</section>

<div class="tz-cenefa"></div>

<!-- Catálogo -->
<section class="container py-5" id="catalogo" aria-labelledby="catalogo-titulo">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <h2 id="catalogo-titulo" class="display-5 mb-1">
                @if(request('q'))
                    Resultados para «{{ request('q') }}»
                @elseif(request('category') && ($current = $categories->firstWhere('id', request('category')) ?? $categories->flatMap->children->firstWhere('id', request('category'))))
                    {{ $current->name }}
                @else
                    Catálogo
                @endif
            </h2>
            <p class="text-muted mb-0">{{ $products->total() }} {{ $products->total() === 1 ? 'prenda' : 'prendas' }}</p>
        </div>

        <form action="{{ route('home') }}#catalogo" method="GET" class="d-flex flex-wrap flex-sm-nowrap gap-2" role="search">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <label for="catalog-search" class="visually-hidden">Buscar prenda</label>
            <div class="input-group">
                <input id="catalog-search" type="search" name="q" class="form-control" placeholder="Buscar prenda" value="{{ request('q') }}">
                <button class="btn btn-dark px-3" type="submit" aria-label="Buscar"><i class="bi bi-search"></i></button>
            </div>
            <label for="catalog-sort" class="visually-hidden">Ordenar</label>
            <select id="catalog-sort" name="sort" class="form-select" onchange="this.form.submit()" style="flex: 1 0 190px;">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Más antiguos</option>
            </select>
        </form>
    </div>

    <!-- Categorías como franja de baldosas: la activa es la más oscura -->
    <nav class="tz-strip mb-2" aria-label="Filtrar por categoría">
        <a href="{{ route('home') }}#catalogo" class="{{ !request('category') ? 'active' : '' }}" @if(!request('category')) aria-current="page" @endif>Todo</a>
        @foreach($categories as $cat)
            @php $isOpen = request('category') == $cat->id || $cat->children->pluck('id')->contains(request('category')); @endphp
            <a href="{{ route('home', ['category' => $cat->id]) }}#catalogo" class="{{ $isOpen ? 'active' : '' }}" @if(request('category') == $cat->id) aria-current="page" @endif>{{ $cat->name }}</a>
        @endforeach
    </nav>
    @foreach($categories as $cat)
        @if($cat->children->isNotEmpty() && (request('category') == $cat->id || $cat->children->pluck('id')->contains(request('category'))))
            <nav class="tz-strip mb-4 small" aria-label="Subcategorías de {{ $cat->name }}">
                <a href="{{ route('home', ['category' => $cat->id]) }}#catalogo" class="{{ request('category') == $cat->id ? 'active' : '' }}">Todo {{ $cat->name }}</a>
                @foreach($cat->children as $child)
                    <a href="{{ route('home', ['category' => $child->id]) }}#catalogo" class="{{ request('category') == $child->id ? 'active' : '' }}">{{ $child->name }}</a>
                @endforeach
            </nav>
        @endif
    @endforeach

    @if($products->isEmpty())
        <div class="border border-dark text-center py-5 px-3 mt-4">
            <p class="tz-serif fs-3 mb-2">
                {{ request('q') || request('category') ? 'Ninguna prenda coincide con tu búsqueda' : 'Aún no hay prendas publicadas' }}
            </p>
            @if(request('q') || request('category'))
                <p class="text-muted">Prueba con otra palabra o mira el catálogo completo.</p>
                <a href="{{ route('home') }}#catalogo" class="btn btn-dark px-4">Ver todo el catálogo</a>
            @endif
        </div>
    @else
        <div class="tz-grid mt-4">
            @foreach($products as $product)
                @include('shop.partials.product-tile', ['product' => $product])
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $products->fragment('catalogo')->links() }}
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Baldosa de compra: deshabilita las combinaciones talla/color sin stock
        const buy = document.getElementById('hero-buy');
        if (buy) {
            const combos = JSON.parse(buy.dataset.variants);
            const sizes = buy.querySelectorAll('input[name="size_id"]');
            const colors = buy.querySelectorAll('input[name="color_id"]');

            function syncColors() {
                const size = buy.querySelector('input[name="size_id"]:checked')?.value;
                colors.forEach(c => {
                    c.disabled = !combos.some(([s, col]) => String(s) === size && String(col) === c.value);
                });
                if (buy.querySelector('input[name="color_id"]:checked:disabled') || !buy.querySelector('input[name="color_id"]:checked')) {
                    const firstEnabled = [...colors].find(c => !c.disabled);
                    if (firstEnabled) firstEnabled.checked = true;
                }
            }

            sizes.forEach(s => s.addEventListener('change', syncColors));
            syncColors();
        }

        // Al pasar el cursor por una prenda se recorren sus fotos
        document.querySelectorAll('.product-image-container').forEach(container => {
            const images = container.querySelectorAll('.hover-slide-img');
            if (images.length <= 1) return;

            const show = idx => images.forEach((img, i) => {
                img.classList.toggle('opacity-100', i === idx);
                img.classList.toggle('opacity-0', i !== idx);
            });

            container.addEventListener('mousemove', e => {
                const rect = container.getBoundingClientRect();
                show(Math.min(images.length - 1, Math.max(0, Math.floor((e.clientX - rect.left) / rect.width * images.length))));
            });
            container.addEventListener('mouseleave', () => show(0));
        });
    });
</script>
@endpush
