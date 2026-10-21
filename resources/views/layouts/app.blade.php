<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#141413">

    <title>@yield('title', 'Tienda de Ropa') | TEZAN</title>
    <meta name="description" content="@yield('meta_description', 'TEZAN: básicos de algodón hechos en Perú, con envíos a todo el país.')">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="tz">
    <a href="#contenido" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 p-2 bg-white z-3">Saltar al contenido</a>

    @include('partials.navbar')

    <main id="contenido">
        @if(session('success') || session('error'))
            <div class="container pt-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            </div>
        @endif

        @hasSection('full')
            @yield('full')
        @else
            <div class="container py-4 py-lg-5">
                @yield('content')
            </div>
        @endif
    </main>

    @include('partials.footer')

    <!-- Bolsa lateral -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="miniCartCanvas" aria-labelledby="miniCartCanvasLabel" style="width: min(420px, 100vw);">
        <div class="offcanvas-header tz-ink py-3">
            <h2 class="offcanvas-title h4 mb-0" id="miniCartCanvasLabel">Tu bolsa</h2>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="tz-cenefa"></div>
        <div class="offcanvas-body d-flex flex-column" id="mini-cart-body">
            @include('shop.partials.mini-cart-items')
        </div>
    </div>

    <!-- Eliminación de ítems desde la bolsa lateral -->
    <script>
        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-cart-item-btn');
            if (!removeBtn) return;

            e.preventDefault();
            removeBtn.disabled = true;

            fetch("{{ route('cart.remove') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ key: removeBtn.dataset.key })
            })
            .then(response => {
                if (!response.ok) throw new Error('No se pudo quitar la prenda');
                return response.json();
            })
            .then(data => {
                document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = data.cart_count);
                document.getElementById('mini-cart-body').innerHTML = data.html;
            })
            .catch(() => {
                removeBtn.disabled = false;
                alert('No se pudo quitar la prenda de la bolsa. Recarga la página e inténtalo de nuevo.');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
