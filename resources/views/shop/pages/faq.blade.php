@extends('layouts.app')

@section('title', 'Preguntas frecuentes')
@section('meta_description', 'Envíos, pagos, pedidos y tallas en TEZAN: respuestas a las preguntas más comunes.')

@php
    $ship = number_format(\App\Support\Cart::SHIPPING_COST, 0);
    $free = number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 0);
    $faqs = [
        ['¿Cuánto cuesta el envío?', "El envío cuesta S/ {$ship} y es gratis en compras desde S/ {$free}. Despachamos a todo el Perú."],
        ['¿Cuánto demora la entrega?', 'Desde que confirmamos tu pago, entregamos en Lima ' . config('tienda.entrega_lima') . ' y en provincias ' . config('tienda.entrega_provincias') . '.'],
        ['¿Cuánto tiempo tengo para pagar?', 'Tienes ' . config('tienda.horas_para_pagar') . ' horas desde que haces el pedido. Después se cancela y las prendas vuelven a estar disponibles.'],
        ['¿Los precios incluyen IGV?', 'Sí. Todos los precios incluyen IGV y emitimos boleta o factura electrónica.'],
        ['¿Qué medios de pago aceptan?', 'Puedes pagar con tarjeta de crédito o débito a través de Mercado Pago (tu pedido se confirma al instante), o con Yape o Plin: te mostramos el monto y el número, nos envías la captura por WhatsApp y validamos el pago.'],
        ['¿Necesito una cuenta para comprar?', 'No. Puedes comprar como invitado. Si creas una cuenta, verás todos tus pedidos en «Mis pedidos».'],
        ['¿Cómo sigo mi pedido?', 'Al comprar recibes un número de pedido que empieza con TZN. Si compraste con tu cuenta, en «Mis pedidos» ves su estado: procesando, en ruta o entregado.'],
        ['¿Cómo sé si hay stock de mi talla?', 'En la página de cada prenda, al elegir el color, las tallas agotadas aparecen tachadas. Si quedan pocas unidades, te lo indicamos.'],
        ['¿Dónde se hacen las prendas?', 'Nuestras prendas son hechas en Perú.'],
        ['¿Puedo cambiar una prenda?', 'Escríbenos a ' . config('tienda.email') . ' con tu número de pedido y te ayudamos.'],
        ['¿Cómo presento un reclamo?', 'Desde nuestro Libro de Reclamaciones virtual. Te enviamos una copia por correo y respondemos en un plazo no mayor a ' . \App\Models\Complaint::RESPONSE_DAYS . ' días hábiles.'],
    ];
@endphp

@push('head')
    @php
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">@json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
        <h1 class="tz-display fs-1 mb-4">Preguntas frecuentes</h1>

        @foreach($faqs as [$question, $answer])
            <details class="tz-details {{ $loop->last ? 'border-bottom' : '' }}" @if($loop->first) open @endif>
                <summary class="fw-semibold">{{ $question }}</summary>
                <p class="pb-3 mb-0">
                    @if($question === '¿Cómo presento un reclamo?')
                        Desde nuestro <a href="{{ route('complaints.create') }}">Libro de Reclamaciones virtual</a>. Te enviamos una copia por correo y respondemos en un plazo no mayor a {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles.
                    @else
                        {{ $answer }}
                    @endif
                </p>
            </details>
        @endforeach

        <p class="mt-5 text-muted">
            ¿No encontraste tu respuesta? Escríbenos a <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>@if(config('tienda.whatsapp')) o por <a href="https://wa.me/{{ config('tienda.whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a>@endif.
        </p>
    </div>
</div>
@endsection
