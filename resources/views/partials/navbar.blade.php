@php $cartCount = \App\Support\Cart::count(); @endphp

<div class="tz-announce tz-label">
    Envío gratis en compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }} a todo el Perú
</div>

<header class="tz-header">
    <div class="tz-header-row">
        <!-- Izquierda: categorías (escritorio) o menú (celular) -->
        <div class="d-flex align-items-center">
            <button class="btn btn-link link-dark p-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuCanvas" aria-controls="menuCanvas" aria-label="Abrir menú">
                <i class="bi bi-list fs-3"></i>
            </button>
            <nav class="tz-nav tz-label d-none d-lg-flex" aria-label="Categorías">
                <a href="{{ route('home', ['sort' => 'newest']) }}#catalogo" class="{{ request()->routeIs('home') && request('sort') === 'newest' ? 'active' : '' }}">Novedades</a>
                @foreach($navCategories as $navCategory)
                    <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo" class="{{ request('category') == $navCategory->id ? 'active' : '' }}">{{ $navCategory->name }}</a>
                @endforeach
            </nav>
        </div>

        <a class="tz-logo" href="{{ route('home') }}" aria-label="TEZAN, inicio">TEZAN</a>

        <!-- Derecha: búsqueda, cuenta y bolsa -->
        <div class="tz-actions tz-label">
            <button type="button" class="d-none d-md-inline" data-bs-toggle="collapse" data-bs-target="#searchBar" aria-expanded="false" aria-controls="searchBar">Buscar</button>

            <div class="dropdown d-none d-md-block">
                <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @auth {{ Str::words(Auth::user()->name, 1, '') }} @else Iniciar sesión @endauth
                </button>
                <ul class="dropdown-menu dropdown-menu-end mt-2" style="text-transform: none; letter-spacing: 0;">
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}">Panel de administración</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
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

            <button type="button" data-bs-toggle="offcanvas" data-bs-target="#miniCartCanvas" aria-controls="miniCartCanvas">
                <span class="d-none d-sm-inline">Bolsa</span><i class="bi bi-bag fs-5 d-sm-none"></i>
                (<span data-cart-count>{{ $cartCount }}</span>)
                <span class="visually-hidden">prendas</span>
            </button>
        </div>
    </div>

    <!-- Barra de búsqueda desplegable -->
    <div class="collapse" id="searchBar">
        <form class="container py-3" action="{{ route('home') }}" method="GET" role="search">
            <label for="nav-search" class="visually-hidden">Buscar prenda</label>
            <div class="input-group">
                <input id="nav-search" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="¿Qué estás buscando?">
                <button type="submit" class="btn btn-dark">Buscar</button>
            </div>
        </form>
    </div>
</header>

<!-- Menú lateral (celular) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="menuCanvas" aria-labelledby="menuCanvasLabel" style="width: min(360px, 90vw);">
    <div class="offcanvas-header border-bottom">
        <span class="tz-logo-sm tz-display fs-3" id="menuCanvasLabel">TEZAN</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('home') }}" method="GET" role="search" class="mb-4">
            <label for="menu-search" class="visually-hidden">Buscar prenda</label>
            <div class="input-group">
                <input id="menu-search" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar prenda">
                <button type="submit" class="btn btn-dark" aria-label="Buscar"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <nav class="tz-menu tz-label" aria-label="Menú">
            <a href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a>
            @foreach($navCategories as $navCategory)
                <a href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
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
