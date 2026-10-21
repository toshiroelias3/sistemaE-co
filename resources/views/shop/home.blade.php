@extends('layouts.app')

@section('title', 'Colección Minimalista')

@section('content')
<!-- HERO SLIDER DE CAMPAÑA - TEZAN -->
<div id="heroTezanCarousel" class="carousel slide carousel-fade mb-5" data-bs-ride="carousel" data-bs-interval="5000">
    <!-- Indicadores Inferiores -->
    <div class="carousel-indicators mb-3">
        <button type="button" data-bs-target="#heroTezanCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroTezanCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    </div>

    <div class="carousel-inner">
        <!-- Slide 1: Colección Principal -->
        <div class="carousel-item active position-relative" style="min-height: 420px; max-height: 550px; background-color: #0d0d0d;">
            <div class="position-absolute top-0 start-0 w-100 h-100" 
                 style="background: linear-gradient(to right, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.3) 60%, rgba(0,0,0,0) 100%), url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat;">
            </div>

            <div class="container h-100 position-relative d-flex align-items-center py-5" style="z-index: 2; min-height: 420px;">
                <div class="row w-100">
                    <div class="col-12 col-md-8 col-lg-6 text-white">
                        <span class="badge bg-white text-dark text-uppercase rounded-0 px-3 py-2 fw-bold tracking-wider mb-3" style="font-size: 0.75rem;">
                            COLECCIÓN ATEMPORAL
                        </span>
                        <h1 class="display-4 fw-bold text-uppercase tracking-wider mb-3" style="letter-spacing: 2px;">
                            ESTÉTICA OLD MONEY
                        </h1>
                        <p class="lead text-white-50 mb-4 fw-light" style="font-size: 0.95rem;">
                            Texturas y fits premium esenciales diseñados para perdurar. Eleva tu estilo diario con versatilidad.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#catalogo" class="btn btn-outline-light rounded-0 px-4 text-uppercase small tracking-wider" style="font-size: 0.75rem;">
                                Ver Catálogo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: Envíos & Servicio -->
        <div class="carousel-item position-relative" style="min-height: 420px; max-height: 550px; background-color: #1a1a1a;">
            <div class="position-absolute top-0 start-0 w-100 h-100" 
                 style="background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0) 100%), url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat;">
            </div>

            <div class="container h-100 position-relative d-flex align-items-center py-5" style="z-index: 2; min-height: 420px;">
                <div class="row w-100">
                    <div class="col-12 col-md-8 col-lg-6 text-white">
                        <span class="badge bg-light text-dark text-uppercase rounded-0 px-3 py-2 fw-bold tracking-wider mb-3" style="font-size: 0.75rem;">
                            DESPACHO A TODO EL PERÚ
                        </span>
                        <h1 class="display-4 fw-bold text-uppercase tracking-wider mb-3" style="letter-spacing: 2px;">
                            Envío gratis en compras de S/200 para todo el Perú
                        </h1>
                        <p class="lead text-white-50 mb-4 fw-light" style="font-size: 0.95rem;">
                            Recibe tus compras en la puerta de tu casa. Pago 100% seguro con Yape o Tarjeta de Crédito/Débito.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#catalogo" class="btn btn-outline-light rounded-0 px-4 text-uppercase small tracking-wider" style="font-size: 0.75rem;">
                                Comprar Ahora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controles de Navegación Flechas -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroTezanCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon p-3 bg-dark bg-opacity-50 border" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroTezanCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon p-3 bg-dark bg-opacity-50 border" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>

<!-- Barra de Beneficios -->
<div class="row row-cols-1 row-cols-md-3 g-3 mb-5 text-center small">
    <div class="col benefit-item">
        <div class="border py-3 px-2 h-100">
            <i class="bi bi-truck d-block mb-1"></i>
            <span class="fw-bold text-uppercase tracking-wider d-block" style="font-size: 0.75rem;">Envío gratis desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</span>
            <span class="text-muted" style="font-size: 0.75rem;">Despachos a todo el Perú</span>
        </div>
    </div>
    <div class="col benefit-item">
        <div class="border py-3 px-2 h-100">
            <i class="bi bi-shield-check d-block mb-1"></i>
            <span class="fw-bold text-uppercase tracking-wider d-block" style="font-size: 0.75rem;">Pago seguro</span>
            <span class="text-muted" style="font-size: 0.75rem;">Tarjeta de crédito, débito o Yape</span>
        </div>
    </div>
    <div class="col benefit-item">
        <div class="border py-3 px-2 h-100">
            <i class="bi bi-arrow-repeat d-block mb-1"></i>
            <span class="fw-bold text-uppercase tracking-wider d-block" style="font-size: 0.75rem;">Cambios sencillos</span>
            <span class="text-muted" style="font-size: 0.75rem;">Te ayudamos si la talla no te queda</span>
        </div>
    </div>
</div>

<!-- Catálogo con Filtro Lateral, Buscador y Hover Carousel -->
<div class="row" id="catalogo">
    
    <!-- Sidebar de Categorías Desplegable -->
    <div class="col-12 col-md-3 mb-4">
        <div class="card border-0 bg-transparent">
            <h6 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Categorías</h6>
            <ul class="list-unstyled small tracking-wide">
                
                <!-- Opción Ver Todo -->
                <li class="mb-2">
                    <a href="{{ route('home') }}" 
                       class="text-decoration-none {{ !request('category') ? 'text-dark fw-bold' : 'text-muted hover-dark' }}">
                        Ver Todo
                    </a>
                </li>
                
                <!-- Lista Dinámica de Categorías -->
                @foreach($categories as $cat)
                    @php
                        $isParentSelected = request('category') == $cat->id;
                        $isChildSelected = $cat->children->pluck('id')->contains(request('category'));
                        $isOpen = $isParentSelected || $isChildSelected;
                    @endphp

                    <li class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Enlace Principal de la Categoría Padre -->
                            <a href="{{ route('home', ['category' => $cat->id]) }}" 
                               class="text-decoration-none {{ $isOpen ? 'text-dark fw-bold' : 'text-muted hover-dark' }}">
                                {{ $cat->name }}
                            </a>

                            <!-- Botón para desplegar/contraer (Solo si tiene subcategorías) -->
                            @if($cat->children && $cat->children->count() > 0)
                                <button class="btn btn-link p-0 text-muted text-decoration-none border-0 ms-2" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse-cat-{{ $cat->id }}" 
                                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                                    <i class="bi bi-chevron-down small" style="font-size: 0.75rem;"></i>
                                </button>
                            @endif
                        </div>

                        <!-- Subcategorías Desplegables (Collapse) -->
                        @if($cat->children && $cat->children->count() > 0)
                            <div class="collapse {{ $isOpen ? 'show' : '' }} ms-3 mt-1" id="collapse-cat-{{ $cat->id }}">
                                <ul class="list-unstyled mb-1">
                                    @foreach($cat->children as $child)
                                        <li class="my-1">
                                            <a href="{{ route('home', ['category' => $child->id]) }}" 
                                               class="text-decoration-none {{ request('category') == $child->id ? 'text-dark fw-bold' : 'text-muted hover-dark' }}" 
                                               style="font-size: 0.8rem;">
                                                — {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endforeach

            </ul>
        </div>
    </div>

    <!-- Sección Principal de Productos -->
    <div class="col-12 col-md-9">
        
        <!-- BARRA SUPERIOR DE BÚSQUEDA Y ORDENAMIENTO -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center pb-3 mb-4 border-bottom gap-3">
            <!-- Indicador de Resultados -->
            <div>
                <span class="small text-uppercase tracking-wider text-muted">
                    @if(request('q'))
                        Resultados para: <strong>"{{ request('q') }}"</strong>
                    @else
                        Mostrando {{ $products->total() }} prendas
                    @endif
                </span>
            </div>

            <!-- Formulario Combinado: Buscador + Selector de Orden -->
            <form action="{{ route('home') }}" method="GET" class="d-flex gap-2 align-items-center">
                <!-- Mantiene el parámetro de categoría si está seleccionado -->
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <!-- Campo de Búsqueda -->
                <div class="input-group input-group-sm">
                    <input type="text" 
                           name="q" 
                           class="form-control rounded-0 border-dark-subtle" 
                           placeholder="Buscar prenda..." 
                           value="{{ request('q') }}">
                    <button class="btn btn-outline-dark rounded-0 px-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

                <!-- Selector de Ordenamiento -->
                <select name="sort" class="form-select form-select-sm rounded-0 border-dark-subtle" onchange="this.form.submit()" style="min-width: 160px;">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más Recientes</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Más Antiguos</option>
                </select>
                
                @if(request('q') || request('sort'))
                    <a href="{{ route('home', request('category') ? ['category' => request('category')] : []) }}" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" title="Limpiar Filtros">
                        <i class="bi bi-x-circle-fill fs-6"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Grilla de Productos con Hover Carousel -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @forelse($products as $product)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-0 product-hover-card">
                        
                        <!-- CONTENEDOR DE LA GALERÍA INTERACTIVA (HOVER CAROUSEL) -->
                        <div class="position-relative overflow-hidden bg-light product-image-container" style="height: 320px; cursor: pointer;">
                            
                            <!-- Indicadores de barra superior (Solo si tiene > 1 foto) -->
                            @if($product->images->count() > 1)
                                <div class="hover-bars-container position-absolute top-0 start-0 w-100 d-flex gap-1 p-2 opacity-0 transition-opacity" style="z-index: 10;">
                                    @foreach($product->images as $index => $img)
                                        <div class="hover-bar flex-grow-1 bg-white {{ $index === 0 ? 'opacity-100' : 'opacity-50' }}" style="height: 3px; transition: opacity 0.2s;"></div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Imágenes superpuestas del producto -->
                            <a href="{{ route('shop.product', $product->slug) }}" class="d-block w-100 h-100">
                                @forelse($product->images as $index => $img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}" 
                                         class="hover-slide-img position-absolute top-0 start-0 w-100 h-100 object-fit-cover {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}" 
                                         style="transition: opacity 0.25s ease-in-out;"
                                         loading="lazy"
                                         alt="{{ $product->name }}">
                                @empty
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small text-uppercase">
                                        <i class="bi bi-image fs-2"></i>
                                    </div>
                                @endforelse
                            </a>

                            <!-- Badge de Esencial / Destacado -->
                            @if($product->is_featured)
                                <span class="position-absolute bottom-0 start-0 bg-dark text-white text-uppercase tracking-wider small px-2 py-1 m-2" style="font-size: 0.6rem; z-index: 5;">
                                    Esencial
                                </span>
                            @endif
                        </div>

                        <!-- Detalles del producto -->
                        <div class="card-body px-3 pt-3 pb-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-medium" style="font-size: 0.95rem;">
                                    <a href="{{ route('shop.product', $product->slug) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
                                </h6>
                                <p class="text-muted small text-truncate mb-2">{{ $product->description ?? 'Diseño exclusivo de temporada.' }}</p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="fw-bold text-dark">S/. {{ number_format($product->base_price, 2) }}</span>
                                <a href="{{ route('shop.product', $product->slug) }}" class="btn btn-sm btn-outline-dark rounded-0 text-uppercase tracking-wider px-3" style="font-size: 0.65rem;">
                                    Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search text-muted display-6"></i>
                    <p class="text-muted small mt-3">
                        @if(request('q') || request('category'))
                            No encontramos prendas con esos filtros.
                        @else
                            No hay prendas disponibles en este momento.
                        @endif
                    </p>
                    @if(request('q') || request('category'))
                        <a href="{{ route('home') }}#catalogo" class="btn btn-outline-dark btn-sm rounded-0 text-uppercase tracking-wider px-4">Ver todo el catálogo</a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Paginación Manteniendo Todos los Filtros -->
        <div class="d-flex justify-content-center mt-5">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lógica del Hover Carousel / Slider al mover el cursor horizontalmente sobre la tarjeta
        const containers = document.querySelectorAll('.product-image-container');

        containers.forEach(container => {
            const images = container.querySelectorAll('.hover-slide-img');
            const bars = container.querySelectorAll('.hover-bar');
            const barsContainer = container.querySelector('.hover-bars-container');

            if (images.length <= 1) return;

            container.addEventListener('mouseenter', function() {
                if (barsContainer) barsContainer.classList.remove('opacity-0');
            });

            container.addEventListener('mouseleave', function() {
                if (barsContainer) barsContainer.classList.add('opacity-0');
                
                images.forEach((img, idx) => {
                    img.classList.toggle('opacity-100', idx === 0);
                    img.classList.toggle('opacity-0', idx !== 0);
                });

                bars.forEach((bar, idx) => {
                    bar.classList.toggle('opacity-100', idx === 0);
                    bar.classList.toggle('opacity-50', idx !== 0);
                });
            });

            container.addEventListener('mousemove', function(e) {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const width = rect.width;
                const totalImages = images.length;

                let activeIndex = Math.floor((x / width) * totalImages);
                if (activeIndex >= totalImages) activeIndex = totalImages - 1;
                if (activeIndex < 0) activeIndex = 0;

                images.forEach((img, idx) => {
                    img.classList.toggle('opacity-100', idx === activeIndex);
                    img.classList.toggle('opacity-0', idx !== activeIndex);
                });

                bars.forEach((bar, idx) => {
                    bar.classList.toggle('opacity-100', idx === activeIndex);
                    bar.classList.toggle('opacity-50', idx !== activeIndex);
                });
            });
        });
    });
</script>
@endpush