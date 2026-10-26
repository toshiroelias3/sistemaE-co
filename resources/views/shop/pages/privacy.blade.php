@extends('layouts.app')

@section('title', 'Política de privacidad')
@section('meta_description', 'Cómo TEZAN recopila, usa y protege tus datos personales.')

{{-- Revisión de cumplimiento (2026-10-25) frente a la Ley N.° 29733, Ley de Protección de Datos Personales, y su reglamento.
     Pendientes del titular: inscribir el banco de datos de clientes ante la Autoridad Nacional de Protección de Datos Personales
     y obtener el visto bueno de un abogado. --}}

@php
    $responsable = config('tienda.razon_social')
        . (config('tienda.ruc') ? ', con RUC ' . config('tienda.ruc') : '')
        . (config('tienda.direccion') ? ' y domicilio en ' . config('tienda.direccion') : '');
@endphp

@section('content')
<div class="row justify-content-center">
    <article class="col-12 col-lg-8 col-xl-7" style="max-width: 72ch;">
        <h1 class="tz-display fs-1 mb-2">Política de privacidad</h1>
        <p class="text-muted small mb-5">Última actualización: 25 de octubre de 2026</p>

        <h2 class="tz-label mt-4 mb-3">1. Responsable</h2>
        <p>
            {{ $responsable }} (en adelante, «TEZAN») es responsable del tratamiento de tus datos personales, conforme a la Ley N.° 29733,
            Ley de Protección de Datos Personales, y su reglamento. Contacto: <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>.
        </p>

        <h2 class="tz-label mt-5 mb-3">2. Qué datos recopilamos</h2>
        <ul>
            <li><strong>Al comprar:</strong> nombre, correo electrónico, dirección de entrega, ciudad o distrito y teléfono.</li>
            <li><strong>Al crear una cuenta:</strong> nombre, correo electrónico y contraseña (guardada cifrada; nadie en TEZAN puede verla).</li>
            <li><strong>En el Libro de Reclamaciones:</strong> nombre, tipo y número de documento, domicilio, teléfono, correo y, si eres menor de edad, el nombre de tu padre, madre o apoderado.</li>
            <li><strong>Al navegar:</strong> solo los datos técnicos necesarios para que la tienda funcione (tu sesión y tu bolsa de compras).</li>
        </ul>
        <p>No recibimos ni guardamos datos de tarjetas: los pagos con tarjeta se hacen en la plataforma de Mercado Pago, y los pagos con Yape o Plin desde tu aplicación.</p>

        <h2 class="tz-label mt-5 mb-3">3. Para qué los usamos</h2>
        <ul>
            <li>Procesar, cobrar y entregar tus pedidos, y avisarte de su estado.</li>
            <li>Emitir el comprobante de pago y cumplir nuestras obligaciones legales y tributarias.</li>
            <li>Atender tus consultas, cambios y reclamos, incluido el Libro de Reclamaciones.</li>
            <li>Mantener tu cuenta y tu historial de pedidos.</li>
        </ul>
        <p>
            Estos usos son necesarios para cumplir la compra que solicitas. No usamos tus datos para publicidad ni los vendemos o alquilamos.
            Si en el futuro quisiéramos enviarte promociones, te pediremos un consentimiento aparte, que podrás retirar cuando quieras.
        </p>

        <h2 class="tz-label mt-5 mb-3">4. Con quién los compartimos</h2>
        <p>Solo con quienes nos ayudan a darte el servicio, y solo con los datos que necesitan:</p>
        <ul>
            <li><strong>Mercado Pago:</strong> tu nombre, correo y el detalle del pedido, cuando pagas con tarjeta.</li>
            <li><strong>Empresas de transporte o courier:</strong> tu nombre, dirección y teléfono, para la entrega.</li>
            <li><strong>Proveedores tecnológicos:</strong> el servicio de alojamiento de la tienda y el de correo electrónico (Google), que guardan la información por encargo nuestro.</li>
            <li><strong>Autoridades:</strong> cuando una norma o una orden lo exija (por ejemplo, SUNAT o INDECOPI).</li>
        </ul>
        <p>
            Algunos de estos proveedores pueden tener servidores fuera del Perú (por ejemplo, el correo electrónico de Google). En esos casos, los datos se
            transfieren solo para las finalidades indicadas y con proveedores que aplican medidas de seguridad adecuadas.
        </p>

        <h2 class="tz-label mt-5 mb-3">5. Cuánto tiempo los conservamos</h2>
        <p>
            Mientras tengas una cuenta o mientras sea necesario para atender tus pedidos y reclamos. Después, solo por el tiempo que exijan las normas
            tributarias y de protección al consumidor; luego los eliminamos o anonimizamos.
        </p>

        <h2 class="tz-label mt-5 mb-3">6. Tus derechos</h2>
        <p>
            Puedes ejercer tus derechos de <strong>acceso, rectificación, cancelación y oposición</strong>, así como revocar tu consentimiento, escribiendo a
            <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a> con el asunto «Datos personales» e indicando tu nombre y lo que solicitas.
            Te responderemos dentro de los plazos de ley. Si consideras que no atendimos tu solicitud, puedes presentar una reclamación ante la
            Autoridad Nacional de Protección de Datos Personales del Ministerio de Justicia y Derechos Humanos.
        </p>

        <h2 class="tz-label mt-5 mb-3">7. Menores de edad</h2>
        <p>
            Nuestra tienda no está dirigida a menores de edad sin autorización de sus padres. Si un menor presenta un reclamo, pedimos el nombre de su
            padre, madre o apoderado, como lo exige la norma del Libro de Reclamaciones.
        </p>

        <h2 class="tz-label mt-5 mb-3">8. Cookies</h2>
        <p>Usamos solo cookies técnicas, necesarias para mantener tu sesión y tu bolsa de compras. No usamos cookies de publicidad ni de seguimiento.</p>

        <h2 class="tz-label mt-5 mb-3">9. Seguridad</h2>
        <p>
            Aplicamos medidas técnicas y organizativas para proteger tus datos: conexión cifrada (https), contraseñas cifradas, acceso restringido al panel
            de administración y protección contra accesos no autorizados.
        </p>

        <h2 class="tz-label mt-5 mb-3">10. Cambios a esta política</h2>
        <p>Si actualizamos esta política, publicaremos aquí la nueva versión con su fecha. Si el cambio es importante, te lo avisaremos por correo.</p>
    </article>
</div>
@endsection
