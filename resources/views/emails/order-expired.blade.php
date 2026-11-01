<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Pedido cancelado {{ $order->reference_number }}</title></head>
<body style="margin:0;padding:24px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111;font-size:14px;">
    <div style="max-width:600px;margin:0 auto;">
        <p style="font-family:Georgia,serif;font-size:26px;letter-spacing:.18em;margin:0 0 20px;">TEZAN</p>
        <p>Hola {{ $order->customer_name }}, cancelamos tu pedido <strong>{{ $order->reference_number }}</strong> porque no registramos el pago dentro de las {{ config('tienda.horas_para_pagar') }} horas.</p>
        <p>Si ya pagaste, escríbenos con tu comprobante a {{ config('tienda.email') }} y lo revisamos. Si aún quieres las prendas, puedes hacer un pedido nuevo en <a href="{{ route('home') }}" style="color:#111;">nuestra tienda</a>.</p>
        <p style="color:#6b6760;font-size:12px;margin-top:24px;">{{ config('tienda.razon_social') }}@if(config('tienda.ruc')), RUC {{ config('tienda.ruc') }}@endif</p>
    </div>
</body>
</html>
