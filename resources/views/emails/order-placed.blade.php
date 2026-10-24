<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Pedido {{ $order->reference_number }}</title></head>
<body style="margin:0;padding:24px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111;font-size:14px;">
    <div style="max-width:600px;margin:0 auto;">
        <p style="font-family:Georgia,serif;font-size:26px;letter-spacing:.18em;margin:0 0 20px;">TEZAN</p>
        <p>Hola {{ $order->customer_name }}, recibimos tu pedido <strong>{{ $order->reference_number }}</strong>.</p>

        <div style="background:#f3f2ef;padding:16px 20px;margin:20px 0;">
            <p style="margin:0 0 8px;font-weight:bold;">Para completar tu compra, págalo con Yape:</p>
            <p style="margin:0 0 4px;">Monto: <strong>S/ {{ number_format($order->total_amount, 2) }}</strong></p>
            @if(config('tienda.yape'))
                <p style="margin:0 0 4px;">Número: <strong>{{ trim(chunk_split(config('tienda.yape'), 3, ' ')) }}</strong> ({{ config('tienda.yape_titular') }})</p>
            @endif
            <p style="margin:0 0 4px;">Mensaje del Yape: <strong>{{ $order->reference_number }}</strong></p>
            <p style="margin:8px 0 0;">
                Luego envíanos la captura
                @if(config('tienda.whatsapp'))
                    por <a href="https://wa.me/{{ config('tienda.whatsapp') }}?text={{ rawurlencode("Hola TEZAN, envío la captura del Yape de mi pedido {$order->reference_number}.") }}" style="color:#111;">WhatsApp</a>.
                @else
                    a {{ config('tienda.email') }}.
                @endif
                Separamos tus prendas mientras validamos el pago.
            </p>
        </div>

        <table style="width:100%;border-collapse:collapse;">
            @foreach($order->items as $item)
                <tr>
                    <td style="padding:8px 0;border-bottom:1px solid #e2ded7;">
                        {{ $item->product->name ?? 'Prenda' }}<br>
                        <span style="color:#6b6760;font-size:12px;">Talla {{ $item->variant->size->name ?? '-' }}, {{ $item->variant->color->name ?? '-' }} · {{ $item->quantity }} u.</span>
                    </td>
                    <td style="padding:8px 0;border-bottom:1px solid #e2ded7;text-align:right;">S/ {{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
            <tr><td style="padding:8px 0;">Envío</td><td style="padding:8px 0;text-align:right;">{{ $order->shipping_cost > 0 ? 'S/ ' . number_format($order->shipping_cost, 2) : 'Gratis' }}</td></tr>
            <tr><td style="padding:8px 0;font-weight:bold;">Total</td><td style="padding:8px 0;text-align:right;font-weight:bold;">S/ {{ number_format($order->total_amount, 2) }}</td></tr>
        </table>

        <p style="margin-top:20px;"><strong>Entrega:</strong> {{ $order->shipping_address }}<br><strong>Teléfono:</strong> {{ $order->phone }}</p>
        <p style="color:#6b6760;font-size:12px;margin-top:24px;">{{ config('tienda.razon_social') }}@if(config('tienda.ruc')), RUC {{ config('tienda.ruc') }}@endif. ¿Dudas? Escríbenos a {{ config('tienda.email') }}.</p>
    </div>
</body>
</html>
