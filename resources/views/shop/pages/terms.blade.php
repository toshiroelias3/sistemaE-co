@extends('layouts.app')

@section('title', 'Términos y condiciones')
@section('meta_description', 'Términos y condiciones de compra en la tienda online de TEZAN.')

{{-- BORRADOR (2026-10-24): redactado a partir de los datos confirmados de la tienda. Debe revisarlo un abogado antes de publicar. --}}

@section('content')
<div class="row justify-content-center">
    <article class="col-12 col-lg-8 col-xl-7" style="max-width: 72ch;">
        <h1 class="tz-display fs-1 mb-2">Términos y condiciones</h1>
        <p class="text-muted small mb-5">Última actualización: 24 de octubre de 2026</p>

        <h2 class="tz-label mt-4 mb-3">1. Quiénes somos</h2>
        <p>
            @php
                $operador = config('tienda.razon_social')
                    . (config('tienda.ruc') ? ', con RUC ' . config('tienda.ruc') : '')
                    . (config('tienda.direccion') ? ' y domicilio en ' . config('tienda.direccion') : '');
            @endphp
            Esta tienda online es operada por {{ $operador }}.
            Puedes contactarnos en <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>@if(config('tienda.whatsapp')) o por <a href="https://wa.me/{{ config('tienda.whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a>@endif.
        </p>

        <h2 class="tz-label mt-5 mb-3">2. Uso de la tienda</h2>
        <p>Al comprar en esta tienda aceptas estos términos. Puedes comprar como invitado o con una cuenta. Si creas una cuenta, eres responsable de mantener tu contraseña en reserva.</p>

        <h2 class="tz-label mt-5 mb-3">3. Productos y precios</h2>
        <p>Los precios se muestran en soles (S/). Las fotos son referenciales; los colores pueden variar ligeramente según la pantalla. La disponibilidad de cada talla y color se muestra en la página de la prenda y está sujeta a stock al momento de confirmar el pedido.</p>

        <h2 class="tz-label mt-5 mb-3">4. Pedidos y pago</h2>
        <p>
            Por ahora el único medio de pago es Yape. Al confirmar tu pedido te mostramos el monto exacto y el número al que debes yapear, y te enviamos esa información por correo.
            Las prendas quedan separadas mientras validamos el pago. El pedido se considera confirmado cuando validamos tu Yape; te avisaremos por correo.
            Si el pago no se realiza, podemos cancelar el pedido y liberar las prendas.
        </p>

        <h2 class="tz-label mt-5 mb-3">5. Envíos</h2>
        <p>
            Despachamos a todo el Perú. El envío cuesta S/ {{ number_format(\App\Support\Cart::SHIPPING_COST, 2) }} y es gratis en compras desde S/ {{ number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 2) }}.
            El costo de envío se muestra antes de confirmar el pedido. Es responsabilidad del cliente indicar una dirección y un teléfono correctos.
        </p>

        <h2 class="tz-label mt-5 mb-3">6. Cambios y devoluciones</h2>
        <p>Si necesitas cambiar una prenda o tienes un problema con tu pedido, escríbenos a <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a> con tu número de pedido y te ayudaremos. Esto no limita los derechos que te reconoce el Código de Protección y Defensa del Consumidor.</p>

        <h2 class="tz-label mt-5 mb-3">7. Libro de Reclamaciones</h2>
        <p>Contamos con un <a href="{{ route('complaints.create') }}">Libro de Reclamaciones virtual</a> conforme a la normativa vigente. Respondemos en un plazo no mayor a {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles.</p>

        <h2 class="tz-label mt-5 mb-3">8. Datos personales</h2>
        <p>Tratamos tus datos según nuestra <a href="{{ route('pages.privacy') }}">Política de privacidad</a>.</p>

        <h2 class="tz-label mt-5 mb-3">9. Ley aplicable</h2>
        <p>Estos términos se rigen por las leyes de la República del Perú.</p>
    </article>
</div>
@endsection
