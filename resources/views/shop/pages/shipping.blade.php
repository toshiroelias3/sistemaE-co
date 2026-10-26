@extends('layouts.app')

@section('title', 'Envíos y pagos')
@section('meta_description', 'Costos de envío a todo el Perú y medios de pago aceptados en TEZAN.')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <h1 class="tz-display fs-1 mb-4">Envíos y pagos</h1>

        <h2 class="tz-label mt-4 mb-3">Envíos</h2>
        <table class="table">
            <tbody>
                <tr><th scope="row" class="fw-normal">Compras menores a S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</th><td class="text-end tz-price">S/ {{ number_format(\App\Support\Cart::SHIPPING_COST, 2) }}</td></tr>
                <tr><th scope="row" class="fw-normal">Compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0) }}</th><td class="text-end fw-semibold">Gratis</td></tr>
            </tbody>
        </table>
        <p>Despachamos a todo el Perú. El costo de envío se muestra en tu bolsa y en el checkout antes de pagar.</p>

        <h2 class="tz-label mt-5 mb-3">Pagos</h2>
        <ul>
            <li><strong>Tarjeta de crédito o débito</strong> a través de Mercado Pago: pagas en su página segura y tu pedido se confirma al instante.</li>
            <li><strong>Yape o Plin</strong>: al confirmar tu pedido te mostramos el monto exacto y el número. Envíanos la captura por WhatsApp con tu número de pedido; separamos tus prendas mientras validamos el pago.</li>
        </ul>
        <p>Todos los precios están en soles (S/).</p>

        <h2 class="tz-label mt-5 mb-3">Seguimiento</h2>
        <p>Al comprar recibes un número de pedido que empieza con TZN. Si compraste con tu cuenta, puedes ver el estado en <a href="{{ route('customer.orders.index') }}">Mis pedidos</a>.</p>

        <p class="mt-5 text-muted">¿Dudas? Revisa las <a href="{{ route('pages.faq') }}">preguntas frecuentes</a> o escríbenos a <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>.</p>
    </div>
</div>
@endsection
