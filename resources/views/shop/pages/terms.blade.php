@extends('layouts.app')

@section('title', 'Términos y condiciones')
@section('meta_description', 'Términos y condiciones de compra en la tienda online de TEZAN.')

{{-- Revisión de cumplimiento (2026-10-25) frente a la Ley 29571 (Código de Protección y Defensa del Consumidor)
     y el reglamento del Libro de Reclamaciones. Pendiente de visto bueno de un abogado. --}}

@php
    $operador = config('tienda.razon_social')
        . (config('tienda.ruc') ? ', con RUC ' . config('tienda.ruc') : '')
        . (config('tienda.direccion') ? ', con domicilio en ' . config('tienda.direccion') : '');
    $envio = number_format(\App\Support\Cart::SHIPPING_COST, 2);
    $envioGratis = number_format(\App\Support\Cart::FREE_SHIPPING_FROM, 2);
@endphp

@section('content')
<div class="row justify-content-center">
    <article class="col-12 col-lg-8 col-xl-7" style="max-width: 72ch;">
        <h1 class="tz-display fs-1 mb-2">Términos y condiciones</h1>
        <p class="text-muted small mb-5">Última actualización: 1 de noviembre de 2026</p>

        <h2 class="tz-label mt-4 mb-3">1. Quiénes somos</h2>
        <p>
            Esta tienda online es operada por {{ $operador }} (en adelante, «TEZAN»).
            Puedes contactarnos en <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>@if(config('tienda.whatsapp')) o por <a href="https://wa.me/{{ config('tienda.whatsapp') }}" target="_blank" rel="noopener">WhatsApp</a>@endif.
        </p>

        <h2 class="tz-label mt-5 mb-3">2. Aceptación</h2>
        <p>
            Al realizar un pedido declaras haber leído y aceptado estos términos y nuestra <a href="{{ route('pages.privacy') }}">Política de privacidad</a>.
            Puedes comprar como invitado o con una cuenta; si creas una cuenta, eres responsable de mantener tu contraseña en reserva.
            Si eres menor de edad, debes comprar con la autorización de tu padre, madre o apoderado.
        </p>

        <h2 class="tz-label mt-5 mb-3">3. Productos, precios y stock</h2>
        <ul>
            <li>Los precios están expresados en soles (S/) y son el <strong>precio final e incluyen IGV</strong>. El costo de envío se muestra por separado antes de confirmar el pedido.</li>
            <li>Las fotos son referenciales: los colores pueden variar ligeramente según la pantalla. En cada prenda se indican las tallas y colores disponibles.</li>
            <li>La compra está sujeta al stock disponible al momento de confirmar el pedido. Si por un error excepcional no pudiéramos entregar una prenda ya pagada, te lo informaremos y te devolveremos el monto pagado por ella.</li>
            <li>Si un precio se publica con un error evidente, te avisaremos antes de despachar y podrás confirmar la compra al precio correcto o anularla con devolución total.</li>
        </ul>

        <h2 class="tz-label mt-5 mb-3">4. Pedidos y medios de pago</h2>
        <ul>
            <li><strong>Tarjeta de crédito o débito:</strong> el pago lo procesa Mercado Pago en su propia plataforma. TEZAN no recibe ni guarda los datos de tu tarjeta.</li>
            <li><strong>Yape o Plin:</strong> al confirmar el pedido te mostramos el monto exacto y el número de destino, y te lo enviamos por correo. Las prendas quedan reservadas durante {{ config('tienda.horas_para_pagar') }} horas.</li>
            <li>El pedido queda confirmado cuando el pago es aprobado por Mercado Pago o validado por TEZAN; te avisaremos por correo.</li>
            <li>Si un pedido no se paga dentro de {{ config('tienda.horas_para_pagar') }} horas, se cancela automáticamente, las prendas se liberan y te avisamos por correo. Si tu pago llega después y aún hay stock, reactivamos el pedido; si no lo hay, te devolvemos el monto pagado.</li>
        </ul>

        <h2 class="tz-label mt-5 mb-3">5. Comprobante de pago</h2>
        <p>
            Por cada compra emitimos boleta o factura electrónica, según las normas de la SUNAT.
            Si necesitas factura, escríbenos con tu número de RUC antes de pagar.
        </p>

        <h2 class="tz-label mt-5 mb-3">6. Envíos y entrega</h2>
        <ul>
            <li>Despachamos a todo el Perú. El envío cuesta S/ {{ $envio }} y es gratis en compras desde S/ {{ $envioGratis }}.</li>
            <li>Desde que confirmamos tu pago, entregamos en Lima {{ config('tienda.entrega_lima') }} y en provincias {{ config('tienda.entrega_provincias') }}. Si no cumplimos el plazo, puedes pedir la anulación del pedido y la devolución de lo pagado.</li>
            <li>Debes indicar una dirección y un teléfono correctos. Si la entrega no es posible por datos errados o ausencia, coordinaremos contigo un nuevo intento.</li>
        </ul>

        <h2 class="tz-label mt-5 mb-3">7. Garantía, cambios y devoluciones</h2>
        <p>
            Todas nuestras prendas cuentan con la garantía legal que reconoce el Código de Protección y Defensa del Consumidor: si una prenda llega con fallas
            de fabricación o no corresponde a lo que compraste, puedes solicitar su reposición, el cambio por otra o la devolución de lo pagado.
            Para cualquier cambio o problema con tu pedido, escríbenos a <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a> con tu número de pedido.
        </p>

        <h2 class="tz-label mt-5 mb-3">8. Libro de Reclamaciones</h2>
        <p>
            Contamos con un <a href="{{ route('complaints.create') }}">Libro de Reclamaciones virtual</a>. Te enviamos una copia de tu hoja por correo
            y respondemos en un plazo no mayor a {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles. Presentar un reclamo no te impide acudir a otras vías
            de solución ni es requisito para presentar una denuncia ante el INDECOPI.
        </p>

        <h2 class="tz-label mt-5 mb-3">9. Propiedad intelectual</h2>
        <p>La marca TEZAN, el logotipo, las fotografías y los textos de esta tienda pertenecen a TEZAN o a sus titulares y no pueden usarse sin autorización.</p>

        <h2 class="tz-label mt-5 mb-3">10. Cambios a estos términos</h2>
        <p>Podemos actualizar estos términos; la versión vigente es la publicada en esta página. Cada pedido se rige por los términos vigentes al momento de realizarlo.</p>

        <h2 class="tz-label mt-5 mb-3">11. Ley aplicable</h2>
        <p>
            Estos términos se rigen por las leyes de la República del Perú, en particular la Ley N.° 29571, Código de Protección y Defensa del Consumidor.
            Ante cualquier controversia puedes acudir al INDECOPI.
        </p>
    </article>
</div>
@endsection
