<footer class="tz-footer mt-auto">
    <div class="tz-cenefa"></div>
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="tz-serif display-6 mb-2">TEZAN</p>
                <p class="text-muted mb-0" style="max-width: 36ch;">Básicos de algodón hechos en Perú, pensados para durar más que una temporada.</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="h6 mb-3">Tienda</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="{{ route('home') }}#catalogo">Catálogo</a></li>
                    <li class="mb-2"><a href="{{ route('cart.index') }}">Tu bolsa</a></li>
                    <li class="mb-2"><a href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="h6 mb-3">Contacto</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="mailto:soporte@tezan.com"><i class="bi bi-envelope me-2"></i>soporte@tezan.com</a></li>
                    <li class="mb-2 text-muted">Pagos con tarjeta o Yape</li>
                </ul>
            </div>
        </div>
        <p class="small text-muted mt-5 mb-0">&copy; {{ date('Y') }} TEZAN S.A.C.</p>
    </div>
</footer>
