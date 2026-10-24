@extends('layouts.app')

@section('title', 'Política de privacidad')
@section('meta_description', 'Cómo TEZAN recopila, usa y protege tus datos personales.')

{{-- BORRADOR (2026-10-24) basado en la Ley N.° 29733. Debe revisarlo un abogado; confirmar también la inscripción del banco de datos ante la Autoridad Nacional de Protección de Datos Personales. --}}

@section('content')
<div class="row justify-content-center">
    <article class="col-12 col-lg-8 col-xl-7" style="max-width: 72ch;">
        <h1 class="tz-display fs-1 mb-2">Política de privacidad</h1>
        <p class="text-muted small mb-5">Última actualización: 24 de octubre de 2026</p>

        <h2 class="tz-label mt-4 mb-3">1. Responsable</h2>
        <p>
            @php
                $responsable = config('tienda.razon_social')
                    . (config('tienda.ruc') ? ', RUC ' . config('tienda.ruc') : '')
                    . (config('tienda.direccion') ? ', con domicilio en ' . config('tienda.direccion') : '');
            @endphp
            {{ $responsable }}, es responsable del tratamiento de tus datos personales conforme a la Ley N.° 29733, Ley de Protección de Datos Personales, y su reglamento.
        </p>

        <h2 class="tz-label mt-5 mb-3">2. Qué datos recopilamos</h2>
        <ul>
            <li><strong>Al comprar:</strong> nombre, correo electrónico, dirección de entrega, ciudad o distrito y teléfono.</li>
            <li><strong>Al crear una cuenta:</strong> nombre, correo electrónico y contraseña (guardada cifrada).</li>
            <li><strong>En el Libro de Reclamaciones:</strong> nombre, tipo y número de documento, domicilio, teléfono, correo y, si eres menor de edad, el nombre de tu padre, madre o apoderado.</li>
        </ul>
        <p>No guardamos datos de tarjetas: los pagos se hacen por Yape, fuera de esta web.</p>

        <h2 class="tz-label mt-5 mb-3">3. Para qué los usamos</h2>
        <ul>
            <li>Procesar, cobrar y entregar tus pedidos, y avisarte de su estado.</li>
            <li>Responder tus consultas y reclamos.</li>
            <li>Mantener tu cuenta y tu historial de pedidos.</li>
        </ul>
        <p>No vendemos ni alquilamos tus datos. Solo compartimos la información necesaria con quienes realizan la entrega de tu pedido.</p>

        <h2 class="tz-label mt-5 mb-3">4. Cuánto tiempo los conservamos</h2>
        <p>Mientras tengas una cuenta o mientras sea necesario para atender tus pedidos y reclamos, y por el tiempo que exijan las obligaciones legales y tributarias.</p>

        <h2 class="tz-label mt-5 mb-3">5. Tus derechos</h2>
        <p>
            Puedes ejercer tus derechos de acceso, rectificación, cancelación y oposición escribiendo a
            <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a> con el asunto «Datos personales».
            Si no recibes respuesta, puedes acudir a la Autoridad Nacional de Protección de Datos Personales.
        </p>

        <h2 class="tz-label mt-5 mb-3">6. Cookies</h2>
        <p>Usamos solo las cookies necesarias para que la tienda funcione: mantener tu sesión y tu bolsa de compras.</p>

        <h2 class="tz-label mt-5 mb-3">7. Seguridad</h2>
        <p>Aplicamos medidas técnicas razonables para proteger tus datos, como el cifrado de contraseñas y la conexión segura del sitio.</p>
    </article>
</div>
@endsection
