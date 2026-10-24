<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Pago confirmado {{ $order->reference_number }}</title></head>
<body style="margin:0;padding:24px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111;font-size:14px;">
    <div style="max-width:600px;margin:0 auto;">
        <p style="font-family:Georgia,serif;font-size:26px;letter-spacing:.18em;margin:0 0 20px;">TEZAN</p>
        <p>Hola {{ $order->customer_name }}, confirmamos tu pago de <strong>S/ {{ number_format($order->total_amount, 2) }}</strong> por el pedido <strong>{{ $order->reference_number }}</strong>.</p>
        <p>Ya estamos preparando tus prendas. Te avisaremos cuando salgan a <strong>{{ $order->shipping_address }}</strong>.</p>
        @if($order->user_id)
            <p><a href="{{ route('customer.orders.show', $order->id) }}" style="color:#111;">Ver el estado de tu pedido</a></p>
        @endif
        <p style="color:#6b6760;font-size:12px;margin-top:24px;">{{ config('tienda.razon_social') }}@if(config('tienda.ruc')), RUC {{ config('tienda.ruc') }}@endif. ¿Dudas? Escríbenos a {{ config('tienda.email') }}.</p>
    </div>
</body>
</html>
