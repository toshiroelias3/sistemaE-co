@php $cartCount = \App\Support\Cart::count(); @endphp

<!-- Barra de anuncio superior -->
<div class="bg-dark text-white text-center small py-2 tracking-wider text-uppercase" style="font-size: 0.7rem;">
    Envío gratis en compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }} a todo el Perú
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold tracking-widest text-uppercase" href="{{ route('home') }}">
            TEZAN
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 text-uppercase small fw-medium">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') && request('sort') === 'newest' ? 'active fw-bold' : '' }}" href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a>
                </li>
                @foreach($navCategories as $navCategory)
                    <li class="nav-item">
                        <a class="nav-link {{ request('category') == $navCategory->id ? 'active fw-bold' : '' }}" href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="d-flex align-items-center gap-3 pb-3 pb-lg-0">
                <form class="d-flex position-relative align-items-center flex-grow-1" action="{{ route('home') }}" method="GET" role="search">
                    <input class="form-control form-control-sm pe-4 rounded-0 border-dark-subtle" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar prenda..." aria-label="Buscar prenda">
                    <button type="submit" class="position-absolute end-0 border-0 bg-transparent me-2 p-0 text-muted small" aria-label="Buscar">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <div class="dropdown">
                    <a href="#" class="text-dark text-decoration-none dropdown-toggle small" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
                        <i class="bi bi-person fs-5"></i>
                        @auth <span class="d-none d-md-inline ms-1">{{ Auth::user()->name }}</span> @endauth
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end rounded-0 shadow-sm border-0 small mt-2">
                        @auth
                            @if(Auth::user()->role === 'admin')
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Panel Admin</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}">
                                    <i class="bi bi-bag me-2"></i> Mis Pedidos
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Salir</button>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item py-2" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('register') }}"><i class="bi bi-person-plus me-2"></i> Crear Cuenta</a></li>
                        @endauth
                    </ul>
                </div>

                <button id="cart-icon-btn"
                        class="btn p-0 position-relative border-0"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#miniCartCanvas"
                        aria-controls="miniCartCanvas"
                        aria-label="Ver mi bolsa"
                        title="Ver mi bolsa">
                    <i class="bi bi-bag text-dark fs-5"></i>
                    <span id="cart-count-badge"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark text-white d-flex align-items-center justify-content-center px-1 {{ $cartCount == 0 ? 'd-none' : '' }}"
                          style="font-size: 0.65rem; width: 16px; height: 16px;">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</nav>
