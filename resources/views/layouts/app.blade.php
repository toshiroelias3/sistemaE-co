<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Tienda de Ropa') | TEZAN</title>
    <meta name="description" content="@yield('meta_description', 'TEZAN: ropa minimalista y atemporal con envíos a todo el Perú.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss'])
    
    @stack('styles')
</head>
<body>
    
    @include('partials.navbar')

    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @include('partials.footer')

    <!-- Offcanvas Mini-Carrito Lateral -->
    <div class="offcanvas offcanvas-end rounded-0 border-0 shadow" tabindex="-1" id="miniCartCanvas" aria-labelledby="miniCartCanvasLabel" style="width: min(400px, 100vw);">
        <div class="offcanvas-header border-bottom py-3">
            <h5 class="offcanvas-title fw-light text-uppercase tracking-wider" id="miniCartCanvasLabel">
                <i class="bi bi-bag me-2"></i> Mi Bolsa
            </h5>
            <button type="button" class="btn-close text-reset rounded-0 small" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column" id="mini-cart-body">
            @include('shop.partials.mini-cart-items')
        </div>
    </div>

    @vite(['resources/js/app.js'])
    
    <!-- Script Global para Manejo de Eventos del Carrito -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar CSRF token para peticiones jQuery si se utiliza en otros scripts
            if (window.jQuery) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            }

            // Escuchador global mediante delegación de eventos para eliminar ítems del mini-carrito
            document.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-cart-item-btn');
                
                if (removeBtn) {
                    e.preventDefault();
                    const cartKey = removeBtn.getAttribute('data-key');

                    if (!cartKey) return;

                    removeBtn.disabled = true;

                    fetch("{{ route('cart.remove') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ key: cartKey })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al eliminar el ítem');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Actualizar el contador del Badge en la barra de navegación
                        const cartBadge = document.getElementById('cart-count-badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                            if (data.cart_count === 0) {
                                cartBadge.classList.add('d-none');
                            } else {
                                cartBadge.classList.remove('d-none');
                            }
                        }

                        // Reemplazar dinámicamente el HTML del mini-carrito
                        const miniCartContainer = document.getElementById('mini-cart-body');
                        if (miniCartContainer) {
                            miniCartContainer.innerHTML = data.html;
                        }
                    })
                    .catch(error => {
                        console.error('Error al eliminar producto:', error);
                        alert('No se pudo eliminar el producto del carrito.');
                    })
                    .finally(() => {
                        removeBtn.disabled = false;
                    });
                }
            });
        });
    </script>

    @stack('scripts')

</body>
</html>