<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Hoja de reclamación {{ $complaint->code }}</title></head>
<body style="margin:0;padding:24px;background:#ffffff;">
    <div style="max-width:640px;margin:0 auto;">
        <p style="font-family:Georgia,serif;font-size:26px;letter-spacing:.18em;margin:0 0 16px;">TEZAN</p>
        <p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
            Hola {{ $complaint->consumer_name }}, registramos tu {{ strtolower($complaint->type_label) }}. Esta es tu copia de la hoja de reclamación.
            Te responderemos en un plazo no mayor a {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles.
        </p>
        <p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;">
            <a href="{{ route('complaints.show', $complaint->token) }}" style="color:#111;">Ver la constancia en línea</a>
        </p>

        @include('shop.complaints.partials.sheet', ['complaint' => $complaint])
    </div>
</body>
</html>
