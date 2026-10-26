@php $redes = array_filter(config('tienda.redes', [])); @endphp
<footer class="tz-footer mt-auto">
    {{-- Con el botón de WhatsApp, espacio extra abajo para que no tape los medios de pago --}}
    <div class="container py-5" @if(config('tienda.whatsapp')) style="padding-bottom: 5.5rem !important;" @endif>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <p class="tz-display fs-2 mb-2" style="letter-spacing: 0.18em;">TEZAN</p>
                <p class="text-muted mb-3" style="max-width: 36ch;">Básicos de algodón hechos en Perú, pensados para durar más que una temporada.</p>
                @if($redes)
                    <div class="d-flex gap-3 fs-5">
                        @foreach(['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $red => $nombre)
                            @if(!empty($redes[$red]))
                                <a href="{{ $redes[$red] }}" target="_blank" rel="noopener" aria-label="TEZAN en {{ $nombre }}"><i class="bi bi-{{ $red }}"></i></a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-6 col-md-2 offset-md-1">
                <h2 class="tz-label mb-3">Tienda</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="{{ route('home', ['sort' => 'newest']) }}#catalogo">Novedades</a></li>
                    <li class="mb-2"><a href="{{ route('home') }}#catalogo">Catálogo</a></li>
                    <li class="mb-2"><a href="{{ route('cart.index') }}">Tu bolsa</a></li>
                    <li class="mb-2"><a href="{{ route('customer.orders.index') }}">Mis pedidos</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-2">
                <h2 class="tz-label mb-3">Ayuda</h2>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><a href="{{ route('pages.faq') }}">Preguntas frecuentes</a></li>
                    <li class="mb-2"><a href="{{ route('pages.shipping') }}">Envíos y pagos</a></li>
                    <li class="mb-2"><a href="{{ route('pages.terms') }}">Términos y condiciones</a></li>
                    <li class="mb-2"><a href="{{ route('pages.privacy') }}">Política de privacidad</a></li>
                    <li class="mb-2"><a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a></li>
                    @if(config('tienda.whatsapp'))
                        <li class="mb-2"><a href="https://wa.me/{{ config('tienda.whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a></li>
                    @endif
                </ul>
            </div>
            <div class="col-12 col-md-3">
                <h2 class="tz-label mb-3">Atención al cliente</h2>
                <a href="{{ route('complaints.create') }}" class="d-inline-flex align-items-center gap-2 border border-dark px-3 py-2 text-decoration-none">
                    <i class="bi bi-journal-text fs-4"></i>
                    <span class="small lh-sm"><strong class="d-block">Libro de Reclamaciones</strong>Registra un reclamo o queja</span>
                </a>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top pt-4 mt-5 small text-muted">
            <span>
                &copy; {{ date('Y') }} {{ config('tienda.razon_social') }}@if(config('tienda.ruc')), RUC {{ config('tienda.ruc') }}@endif
            </span>
            <span class="d-flex align-items-center gap-2" aria-label="Medios de pago">
                <span>Pagas con</span>
                @foreach(['Mercado Pago', 'Yape', 'Plin'] as $medio)
                    <span class="border px-2 py-1 bg-white text-dark fw-semibold" style="font-size: 0.75rem;">{{ $medio }}</span>
                @endforeach
            </span>
        </div>
    </div>
</footer>

@if(config('tienda.whatsapp'))
    <a href="https://wa.me/{{ config('tienda.whatsapp') }}?text={{ rawurlencode('Hola TEZAN, tengo una consulta') }}"
       class="tz-whatsapp" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
@endif
