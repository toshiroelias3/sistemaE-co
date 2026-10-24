@extends('layouts.app')

@section('title', 'Hoja de reclamación ' . $complaint->code)
@section('noindex', '1')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 d-print-none">
            <h1 class="tz-display fs-2 mb-0">Constancia de tu hoja</h1>
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimir o guardar PDF</button>
        </div>
        <p class="text-muted small d-print-none">Guarda este enlace para consultar la respuesta. Es privado: no lo compartas.</p>

        @include('shop.complaints.partials.sheet', ['complaint' => $complaint])
    </div>
</div>
@endsection
