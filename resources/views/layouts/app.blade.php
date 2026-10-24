<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#141413">

    <title>@yield('title', 'Tienda de Ropa') | TEZAN</title>
    <meta name="description" content="@yield('meta_description', 'TEZAN: básicos de algodón hechos en Perú, con envíos a todo el país.')">

    {{-- SEO: las páginas privadas o de búsqueda definen @section('noindex') --}}
    @hasSection('noindex')
        <meta name="robots" content="noindex, follow">
    @else
        <link rel="canonical" href="@yield('canonical', url()->current())">
    @endif

    {{-- Vista previa al compartir (WhatsApp, Instagram, Facebook, X) --}}
    <meta property="og:site_name" content="TEZAN">
    <meta property="og:locale" content="es_PE">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Tienda de Ropa') | TEZAN">
    <meta property="og:description" content="@yield('meta_description', 'TEZAN: básicos de algodón hechos en Perú, con envíos a todo el país.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @stack('head')

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
        <div class="offcanvas-header border-bottom py-3">
            <h2 class="offcanvas-title tz-label mb-0" id="miniCartCanvasLabel">Tu bolsa</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
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

    <!-- Tarjetas de prenda: al pasar el cursor se recorren sus fotos (las alternativas se descargan recién ahí) -->
    <script>
        document.querySelectorAll('.product-image-container').forEach(container => {
            const images = container.querySelectorAll('.hover-slide-img');
            if (images.length <= 1) return;

            container.addEventListener('mouseenter', () => images.forEach(img => {
                if (img.dataset.src) {
                    if (img.dataset.srcset) img.srcset = img.dataset.srcset;
                    img.src = img.dataset.src;
                    delete img.dataset.src;
                }
            }), { once: true });

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
    </script>

    @stack('scripts')
</body>
</html>
