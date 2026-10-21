@php $cartCount = \App\Support\Cart::count(); @endphp

<div class="tz-ink text-center small py-2">
    Envío gratis desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}, con despacho a todo el Perú
</div>

<header class="tz-header">
    <div class="tz-header-row">
        <a class="tz-brand" href="{{ route('home') }}" aria-label="TEZAN, inicio">TEZAN</a>

        <!-- Navegación (escritorio) -->
        <nav class="d-none d-lg-flex" aria-label="Principal">
            <a class="tz-nav-link {{ request()->routeIs('home') && request('sort') === 'newest' ? 'active' : '' }}" href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a>
            @foreach($navCategories as $navCategory)
                <a class="tz-nav-link {{ request('category') == $navCategory->id ? 'active' : '' }}" href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
            @endforeach
        </nav>

        <div class="ms-auto d-flex align-items-stretch">
            <form class="d-none d-md-flex align-items-center px-3" action="{{ route('home') }}" method="GET" role="search">
                <label for="nav-search" class="visually-hidden">Buscar prenda</label>
                <div class="input-group input-group-sm">
                    <input id="nav-search" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar prenda">
                    <button type="submit" class="btn btn-dark" aria-label="Buscar"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <div class="dropdown d-flex">
                <button class="tz-nav-link border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person fs-5"></i>
                    <span class="d-none d-xl-inline ms-2">@auth {{ Str::words(Auth::user()->name, 1, '') }} @else Ingresar @endauth</span>
                    <span class="visually-hidden">Mi cuenta</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end rounded-0 border-dark mt-0">
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Panel de administración</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a class="dropdown-item py-2" href="{{ route('customer.orders.index') }}"><i class="bi bi-receipt me-2"></i>Mis pedidos</a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                            </form>
                        </li>
                    @else
                        <li><a class="dropdown-item py-2" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('register') }}"><i class="bi bi-person-plus me-2"></i>Crear cuenta</a></li>
                    @endauth
                </ul>
            </div>

            <button class="tz-bag" type="button" data-bs-toggle="offcanvas" data-bs-target="#miniCartCanvas" aria-controls="miniCartCanvas">
                <i class="bi bi-bag fs-5"></i>
                <span class="d-none d-sm-inline">Bolsa</span>
                <span data-cart-count>{{ $cartCount }}</span>
                <span class="visually-hidden">prendas</span>
            </button>
        </div>
    </div>

    <!-- Navegación (celular y tablet) -->
    <nav class="d-flex d-lg-none overflow-auto" style="background: var(--tz-white-deep);" aria-label="Categorías">
        <a class="tz-nav-link py-2 flex-shrink-0 {{ request()->routeIs('home') && request('sort') === 'newest' ? 'active' : '' }}" href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a>
        @foreach($navCategories as $navCategory)
            <a class="tz-nav-link py-2 flex-shrink-0 {{ request('category') == $navCategory->id ? 'active' : '' }}" href="{{ route('home', ['category' => $navCategory->id]) }}#catalogo">{{ $navCategory->name }}</a>
        @endforeach
        <a class="tz-nav-link py-2 flex-shrink-0 d-md-none" href="{{ route('home') }}#catalogo"><i class="bi bi-search me-1"></i>Buscar</a>
    </nav>
</header>
