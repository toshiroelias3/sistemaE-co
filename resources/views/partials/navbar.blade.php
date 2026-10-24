@php
    $cartCount = \App\Support\Cart::count();
    // En la portada la cabecera flota transparente sobre el banner
    $overlay = request()->routeIs('home');
@endphp

<div class="tz-top {{ $overlay ? 'tz-top--overlay' : '' }}">
    <div class="tz-announce">
        Envíos gratis a partir de S/{{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 2) }} a todo el Perú
    </div>

    <header class="tz-header" id="site-header">
        <div class="tz-header-row">
            <a class="tz-logo" href="{{ route('home') }}" aria-label="TEZAN, inicio">TEZAN</a>

            <!-- Menú principal (escritorio) -->
            <nav class="tz-menu-bar d-none d-lg-flex" aria-label="Principal">
                <div class="tz-menu-item">
                    <a href="{{ route('home') }}#catalogo" class="{{ request()->routeIs('home') && !request('category') && !request('sort') ? 'active' : '' }}">
                        Ver todo <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="tz-submenu">
                        <a href="{{ route('home') }}#catalogo">Toda la colección</a>
                        @foreach($navCategories as $navCategory)
                            <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="tz-menu-item">
                    <a href="{{ route('home', ['sort' => 'newest']) }}#catalogo" class="{{ request('sort') === 'newest' ? 'active' : '' }}">Novedades</a>
                </div>

                @foreach($navCategories as $navCategory)
                    @php $isActive = request('category') == $navCategory->id || $navCategory->children->pluck('id')->contains(request('category')); @endphp
                    <div class="tz-menu-item">
                        <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo" class="{{ $isActive ? 'active' : '' }}">
                            {{ $navCategory->name }}
                            @if($navCategory->children->isNotEmpty()) <i class="bi bi-chevron-down"></i> @endif
                        </a>
                        @if($navCategory->children->isNotEmpty())
                            <div class="tz-submenu">
                                <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">Todo {{ $navCategory->name }}</a>
                                @foreach($navCategory->children as $child)
                                    <a href="{{ route('home', ['category' => $child->id]) }}#catalogo">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>

            <!-- Íconos: buscar, cuenta, bolsa y menú (celular) -->
            <div class="tz-icons">
                <button type="button" class="tz-icon" data-bs-toggle="collapse" data-bs-target="#searchBar" aria-expanded="false" aria-controls="searchBar" aria-label="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <div class="dropdown d-none d-lg-block">
                    <button type="button" class="tz-icon" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
                        <i class="bi bi-person"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end mt-2">
                        @auth
                            <li><span class="dropdown-item-text small text-muted">Hola, {{ Str::words(Auth::user()->name, 1, '') }}</span></li>
                            @if(Auth::user()->role === 'admin')
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}">Panel de administración</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2">Cerrar sesión</button>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item py-2" href="{{ route('login') }}">Iniciar sesión</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('register') }}">Crear cuenta</a></li>
                        @endauth
                    </ul>
                </div>

                <button type="button" class="tz-icon" data-bs-toggle="offcanvas" data-bs-target="#miniCartCanvas" aria-controls="miniCartCanvas">
                    <i class="bi bi-bag"></i>
                    <span class="tz-count" data-cart-count>{{ $cartCount }}</span>
                    <span class="visually-hidden">prendas en tu bolsa</span>
                </button>

                <button type="button" class="tz-icon d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#menuCanvas" aria-controls="menuCanvas" aria-label="Abrir menú">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <!-- Barra de búsqueda desplegable -->
        <div class="collapse" id="searchBar" style="background: var(--tz-paper);">
            <form class="container py-3" action="{{ route('home') }}" method="GET" role="search">
                <label for="nav-search" class="visually-hidden">Buscar prenda</label>
                <div class="input-group">
                    <input id="nav-search" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="¿Qué estás buscando?">
                    <button type="submit" class="btn btn-dark">Buscar</button>
                </div>
            </form>
        </div>
    </header>
</div>

<!-- Menú lateral (celular) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="menuCanvas" aria-labelledby="menuCanvasLabel" style="width: min(360px, 90vw);">
    <div class="offcanvas-header border-bottom">
        <span class="tz-display fs-3" id="menuCanvasLabel" style="letter-spacing: 0.16em;">TEZAN</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="tz-menu tz-label" aria-label="Menú">
            <a href="{{ route('home') }}#catalogo">Ver todo</a>
            <a href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a>
            @foreach($navCategories as $navCategory)
                <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
                @if($navCategory->children->isNotEmpty())
                    <div class="tz-menu-sub">
                        @foreach($navCategory->children as $child)
                            <a href="{{ route('home', ['category' => $child->id]) }}#catalogo">{{ $child->name }}</a>
                        @endforeach
                    </div>
                @endif
            @endforeach
            @auth
                <a href="{{ route('customer.orders.index') }}">Mis pedidos</a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Panel de administración</a>
                @endif
            @else
                <a href="{{ route('login') }}">Iniciar sesión</a>
                <a href="{{ route('register') }}">Crear cuenta</a>
            @endauth
        </nav>
        @auth
            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-outline-dark w-100">Cerrar sesión</button>
            </form>
        @endauth
    </div>
</div>

@if($overlay)
    <script>
        // Al bajar, la cabecera transparente se fija arriba en blanco
        (function () {
            const header = document.getElementById('site-header');
            const top = header.closest('.tz-top');
            const update = () => header.classList.toggle('is-solid', window.scrollY > top.offsetHeight
                || document.getElementById('searchBar').classList.contains('show'));
            window.addEventListener('scroll', update, { passive: true });
            document.getElementById('searchBar').addEventListener('show.bs.collapse', () => header.classList.add('is-solid'));
            document.getElementById('searchBar').addEventListener('hidden.bs.collapse', update);
            update();
        })();
    </script>
@endif
