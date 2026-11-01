{{-- Instrucciones para pagar un pedido por Yape. Recibe $order. --}}
@php
    $whatsappText = "Hola TEZAN, envío la captura del pago (Yape/Plin) de mi pedido {$order->reference_number} por S/ " . number_format($order->total_amount, 2) . '.';
@endphp
<div class="p-4 mb-4 text-start" style="background: var(--tz-warm, #f3f2ef);">
    <p class="tz-label mb-3">Paga tu pedido con Yape o Plin</p>
    <div class="row g-3 align-items-center">
        <div class="col-12 col-md-6">
            <p class="small text-muted mb-1">Monto a pagar</p>
            <p class="fs-2 fw-semibold mb-3 tz-price">S/ {{ number_format($order->total_amount, 2) }}</p>
            @if(config('tienda.yape'))
                <p class="small text-muted mb-1">Número de Yape y Plin</p>
                <p class="fs-4 fw-semibold mb-1">{{ trim(chunk_split(config('tienda.yape'), 3, ' ')) }}</p>
                <p class="small mb-0">A nombre de {{ config('tienda.yape_titular') }}</p>
            @endif
        </div>
        <div class="col-12 col-md-6">
            <ol class="small mb-3 ps-3">
                <li>Abre Yape o Plin y paga el monto exacto.</li>
                <li>En el mensaje escribe <strong>{{ $order->reference_number }}</strong>.</li>
                <li>Envíanos la captura por WhatsApp.</li>
            </ol>
            @if(config('tienda.whatsapp'))
                <a href="https://wa.me/{{ config('tienda.whatsapp') }}?text={{ rawurlencode($whatsappText) }}" target="_blank" rel="noopener" class="btn btn-dark w-100">
                    <i class="bi bi-whatsapp me-2"></i>Enviar captura por WhatsApp
                </a>
            @else
                <p class="small mb-0">Envía la captura a <a href="mailto:{{ config('tienda.email') }}">{{ config('tienda.email') }}</a>.</p>
            @endif
        </div>
    </div>
    <p class="small text-muted mt-3 mb-0">Separamos tus prendas durante {{ config('tienda.horas_para_pagar') }} horas; si no recibimos el pago, el pedido se cancela. Te avisaremos por correo cuando lo confirmemos.</p>
</div>
