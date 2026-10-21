<footer class="tz-footer mt-auto">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-12 col-md-5">
                <p class="tz-display fs-2 mb-2" style="letter-spacing: 0.18em;">TEZAN</p>
                <p class="text-muted mb-0" style="max-width: 36ch;">Básicos de algodón hechos en Perú, pensados para durar más que una temporada.</p>
            </div>
            <div class="col-6 col-md-3 offset-md-1">
                <h2 class="tz-label mb-3">Tienda</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a></li>
                    <li class="mb-2"><a href="{{ route('home') }}#catalogo">Catálogo</a></li>
                    <li class="mb-2"><a href="{{ route('cart.index') }}">Tu bolsa</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="tz-label mb-3">Ayuda</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
                    <li class="mb-2"><a href="mailto:soporte@tezan.com">soporte@tezan.com</a></li>
                    <li class="mb-2 text-muted">Pagos con tarjeta o Yape</li>
                </ul>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between gap-2 border-top pt-4 mt-5 small text-muted">
            <span>&copy; {{ date('Y') }} TEZAN S.A.C.</span>
            <span>Hecho en Perú</span>
        </div>
    </div>
</footer>
