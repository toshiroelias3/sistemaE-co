<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Panel Admin - TEZAN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss'])
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #1a1a1a; width: 260px; position: fixed; top: 0; left: 0; z-index: 100; transition: all 0.3s; }
        .main-content { margin-left: 260px; padding: 2rem; transition: all 0.3s; }
        .sidebar .nav-link { color: #b3b3b3; font-size: 0.9rem; padding: 0.8rem 1.5rem; display: flex; align-items: center; gap: 10px; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff; background-color: #2d2d2d; }
        @media (max-width: 768px) {
            .sidebar { margin-left: -260px; }
            .sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="px-4 py-4 border-bottom border-secondary border-opacity-25">
            <h5 class="text-white fw-bold tracking-widest text-uppercase m-0">TEZAN ADMIN</h5>
        </div>
        <div class="nav flex-column py-3">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ Request::is('admin/products*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Productos
            </a>
            
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::is('admin/categories*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categorías
            </a>

            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ Request::is('admin/orders*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Pedidos
            </a>
            <a href="{{ route('admin.customers.index') }}" class="nav-link {{ Request::is('admin/customers*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Clientes
            </a>
            
            <a href="{{ route('home') }}" class="nav-link border-top border-secondary border-opacity-10 mt-4">
                <i class="bi bi-arrow-left-short"></i> Ver Tienda
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <button class="btn btn-outline-dark d-md-none rounded-0" id="toggleSidebar" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small"><i class="bi bi-shield-check text-success"></i> {{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 text-uppercase tracking-wider small px-3">Salir</button>
                </form>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-0 border-0 shadow-sm py-2 small mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-0 border-0 shadow-sm py-2 small mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('admin_content')
    </div>

@vite(['resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>

    @stack('scripts')

</body>
</html>