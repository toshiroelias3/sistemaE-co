{{-- Hoja de reclamación. Estilos en línea para que también se vea bien en el correo. --}}
@php
    $th = 'text-align:left;padding:8px 10px;background:#f3f2ef;font-weight:600;width:38%;vertical-align:top;border-bottom:1px solid #e2ded7;';
    $td = 'padding:8px 10px;vertical-align:top;border-bottom:1px solid #e2ded7;';
    $h = 'font-size:13px;letter-spacing:.06em;text-transform:uppercase;margin:24px 0 8px;';
@endphp
<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
    <p style="margin:0 0 4px;"><strong>Hoja de reclamación N.° {{ $complaint->code }}</strong></p>
    <p style="margin:0;">Fecha: {{ $complaint->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</p>

    <p style="{{ $h }}">Proveedor</p>
    <table style="width:100%;border-collapse:collapse;">
        <tr><th style="{{ $th }}">Razón social</th><td style="{{ $td }}">{{ config('tienda.razon_social') }}</td></tr>
        <tr><th style="{{ $th }}">RUC</th><td style="{{ $td }}">{{ config('tienda.ruc') ?: '—' }}</td></tr>
        <tr><th style="{{ $th }}">Dirección</th><td style="{{ $td }}">{{ config('tienda.direccion') ?: '—' }}</td></tr>
    </table>

    <p style="{{ $h }}">1. Consumidor</p>
    <table style="width:100%;border-collapse:collapse;">
        <tr><th style="{{ $th }}">Nombre</th><td style="{{ $td }}">{{ $complaint->consumer_name }}</td></tr>
        <tr><th style="{{ $th }}">Documento</th><td style="{{ $td }}">{{ $complaint->document_type }} {{ $complaint->document_number }}</td></tr>
        <tr><th style="{{ $th }}">Domicilio</th><td style="{{ $td }}">{{ $complaint->address }}</td></tr>
        <tr><th style="{{ $th }}">Teléfono</th><td style="{{ $td }}">{{ $complaint->phone }}</td></tr>
        <tr><th style="{{ $th }}">Correo</th><td style="{{ $td }}">{{ $complaint->email }}</td></tr>
        @if($complaint->is_minor)
            <tr><th style="{{ $th }}">Padre, madre o apoderado</th><td style="{{ $td }}">{{ $complaint->guardian_name }}</td></tr>
        @endif
    </table>

    <p style="{{ $h }}">2. Bien contratado</p>
    <table style="width:100%;border-collapse:collapse;">
        <tr><th style="{{ $th }}">Tipo</th><td style="{{ $td }}">{{ \App\Models\Complaint::ITEM_TYPES[$complaint->item_type] ?? $complaint->item_type }}</td></tr>
        <tr><th style="{{ $th }}">N.° de pedido</th><td style="{{ $td }}">{{ $complaint->order_reference ?: '—' }}</td></tr>
        <tr><th style="{{ $th }}">Monto reclamado</th><td style="{{ $td }}">{{ $complaint->amount !== null ? 'S/ ' . number_format($complaint->amount, 2) : '—' }}</td></tr>
        <tr><th style="{{ $th }}">Descripción</th><td style="{{ $td }}">{{ $complaint->item_description }}</td></tr>
    </table>

    <p style="{{ $h }}">3. Detalle de la reclamación</p>
    <table style="width:100%;border-collapse:collapse;">
        <tr><th style="{{ $th }}">Tipo</th><td style="{{ $td }}">{{ $complaint->type_label }}</td></tr>
        <tr><th style="{{ $th }}">Detalle</th><td style="{{ $td }}white-space:pre-line;">{{ $complaint->detail }}</td></tr>
        <tr><th style="{{ $th }}">Pedido del consumidor</th><td style="{{ $td }}white-space:pre-line;">{{ $complaint->consumer_request }}</td></tr>
    </table>

    <p style="{{ $h }}">4. Observaciones y acciones del proveedor</p>
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <th style="{{ $th }}">Respuesta</th>
            <td style="{{ $td }}white-space:pre-line;">{{ $complaint->response ?: 'Pendiente. El proveedor responderá en un plazo no mayor a ' . \App\Models\Complaint::RESPONSE_DAYS . ' días hábiles.' }}</td>
        </tr>
        @if($complaint->responded_at)
            <tr><th style="{{ $th }}">Fecha de respuesta</th><td style="{{ $td }}">{{ $complaint->responded_at->timezone('America/Lima')->format('d/m/Y') }}</td></tr>
        @endif
    </table>

    <p style="font-size:12px;color:#6b6760;margin-top:20px;">
        <strong>Reclamo:</strong> disconformidad relacionada con los productos o servicios.
        <strong>Queja:</strong> disconformidad no relacionada con los productos o servicios, o malestar o descontento respecto a la atención al público.
        La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.
    </p>
</div>
