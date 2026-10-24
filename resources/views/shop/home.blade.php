@extends('layouts.app')

@section('title', 'Básicos de algodón hechos en Perú')
@section('og_image', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1200&auto=format&fit=crop')

{{-- SEO: el orden no crea páginas nuevas; la categoría y la página sí. Las búsquedas no se indexan. --}}
@if(request()->filled('q'))
    @section('noindex', '1')
@else
    @section('canonical', route('home', array_filter([
        'genero' => $gender,
        'category' => request('category'),
        'page' => request('page') > 1 ? request('page') : null,
    ])))
@endif

@section('full')
<!-- Banner de campaña -->
<section class="tz-hero" aria-labelledby="hero-title">
    {{-- Foto de campaña de stock (Unsplash, photo-1490481651871). Reemplazar por una sesión propia de TEZAN. --}}
    @php $hero = 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=75&auto=format&fit=crop&w='; @endphp
    <img src="{{ $hero }}1600"
         srcset="{{ $hero }}640 640w, {{ $hero }}1024 1024w, {{ $hero }}1600 1600w, {{ $hero }}2200 2200w"
         sizes="100vw"
         alt="Prendas de la colección TEZAN colgadas en un perchero" fetchpriority="high">
    <div class="tz-hero-text">
        <h1 id="hero-title">Básicos hechos en Perú</h1>
        <p class="mb-4" style="max-width: 42ch;">Algodón de calidad, buen fit y precios justos. Despacho a todo el Perú.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="#catalogo" class="btn btn-light">Ver colección</a>
            @if($featured)
                <a href="{{ route('shop.product', $featured->slug) }}" class="btn btn-outline-light">Comprar {{ $featured->name }}</a>
            @endif
        </div>
    </div>
</section>

<!-- Banners de sección: Varones y Mujeres -->
@if($editorial->count() >= 2 && !request('q') && !request('category') && !$gender)
    <section class="tz-editorial px-0 mt-1" aria-label="Secciones">
        @foreach($editorial as $item)
            <a href="{{ $item['link'] }}">
                <img src="{{ $item['image']->url(1200) }}" srcset="{{ $item['image']->srcset() }}" sizes="(min-width: 768px) 50vw, 100vw" alt="" loading="lazy" decoding="async">
                <span>
                    <span class="d-block tz-display fs-2">{{ $item['title'] }}</span>
                    <span class="d-block tz-label mt-1">Comprar ahora</span>
                </span>
            </a>
        @endforeach
    </section>
@endif

<div class="pt-4 pt-lg-5"></div>

<!-- Filas por categoría -->
@foreach($rows as $row)
    <section class="container-fluid px-3 px-lg-5 mb-5" aria-labelledby="fila-{{ $loop->index }}">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h2 id="fila-{{ $loop->index }}" class="tz-display fs-2 mb-0">{{ $row['title'] }}</h2>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ $row['link'] }}" class="tz-label link-dark">Ver todo</a>
                <div class="tz-row-arrows d-none d-lg-flex gap-1">
                    <button type="button" data-row-scroll="-1" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" data-row-scroll="1" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>
        <div class="tz-row" tabindex="0" aria-label="{{ $row['title'] }}">
            @foreach($row['products'] as $product)
                @include('shop.partials.product-tile', ['product' => $product])
            @endforeach
        </div>
    </section>
@endforeach

<!-- Catálogo -->
<section class="container-fluid px-3 px-lg-5 pb-5" id="catalogo" aria-labelledby="catalogo-titulo">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h2 id="catalogo-titulo" class="tz-display fs-1 mb-1">
                @if(request('q'))
                    Resultados para «{{ request('q') }}»
                @elseif(request('category') && ($current = $categories->firstWhere('id', request('category')) ?? $categories->flatMap->children->firstWhere('id', request('category'))))
                    {{ $current->name }}{{ $gender ? ' para ' . strtolower(\App\Models\Product::SECTIONS[$gender]) : '' }}
                @elseif($gender)
                    {{ \App\Models\Product::SECTIONS[$gender] }}
                @elseif(request('sort') === 'newest')
                    Novedades
                @else
                    Toda la colección
                @endif
            </h2>
            <p class="text-muted small mb-0">{{ $products->total() }} {{ $products->total() === 1 ? 'prenda' : 'prendas' }}</p>
        </div>

        <form action="{{ route('home') }}#catalogo" method="GET" class="d-flex align-items-center gap-2">
            @foreach(['genero', 'category', 'q'] as $keep)
                @if(request($keep))
                    <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
                @endif
            @endforeach
            <label for="catalog-sort" class="tz-label text-muted text-nowrap">Ordenar por</label>
            <select id="catalog-sort" name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más recientes</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
            </select>
        </form>
    </div>

    <!-- Categorías de la sección actual (o de toda la tienda) -->
    @unless(request('q'))
        <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Categorías">
            <a href="{{ route('home', array_filter(['genero' => $gender])) }}#catalogo" class="btn btn-sm {{ !request('category') ? 'btn-dark' : 'btn-outline-dark' }}">
                Todo{{ $gender ? ' ' . strtolower(\App\Models\Product::SECTIONS[$gender]) : '' }}
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('home', array_filter(['genero' => $gender, 'category' => $cat->id])) }}#catalogo" class="btn btn-sm {{ request('category') == $cat->id ? 'btn-dark' : 'btn-outline-dark' }}">{{ $cat->name }}</a>
            @endforeach
        </nav>
    @endunless

    @if($products->isEmpty())
        <div class="text-center py-5">
            <p class="tz-display fs-3 mb-2">
                {{ request('q') || request('category') ? 'No encontramos prendas con esa búsqueda' : 'Muy pronto, nueva colección' }}
            </p>
            @if(request('q') || request('category'))
                <p class="text-muted">Prueba con otra palabra o mira toda la colección.</p>
                <a href="{{ route('home') }}#catalogo" class="btn btn-dark">Ver toda la colección</a>
            @endif
        </div>
    @else
        <div class="tz-grid">
            @foreach($products as $product)
                @include('shop.partials.product-tile', ['product' => $product])
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $products->fragment('catalogo')->links() }}
        </div>
    @endif
</section>

<!-- Beneficios -->
<section class="tz-benefits py-5 mt-5" aria-label="Beneficios">
    <div class="container">
        <div class="row row-cols-1 row-cols-md-3 g-4 text-center">
            <div class="col">
                <i class="bi bi-truck d-block mb-2"></i>
                <p class="tz-label mb-1">Envío gratis desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</p>
                <p class="small text-muted mb-0">Despachamos a todo el Perú. Si no, el envío cuesta S/ {{ number_format(\App\Support\Cart::SHIPPING_COST, 0) }}.</p>
            </div>
            <div class="col">
                <i class="bi bi-credit-card d-block mb-2"></i>
                <p class="tz-label mb-1">Paga con Yape</p>
                <p class="small text-muted mb-0">Yapea al confirmar tu pedido y envíanos la captura.</p>
            </div>
            <div class="col">
                <i class="bi bi-geo-alt d-block mb-2"></i>
                <p class="tz-label mb-1">Hecho en Perú</p>
                <p class="small text-muted mb-0">Básicos confeccionados en el país.</p>
            </div>
        </div>
    </div>
</section>
@endsection


@push('scripts')
<script>
    // Flechas de las filas deslizables
    document.querySelectorAll('[data-row-scroll]').forEach(button => {
        const row = button.closest('section').querySelector('.tz-row');
        button.addEventListener('click', () => row.scrollBy({ left: row.clientWidth * button.dataset.rowScroll, behavior: 'smooth' }));
    });
</script>
@endpush
