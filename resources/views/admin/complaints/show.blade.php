@extends('layouts.admin')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-light text-uppercase tracking-wider m-0">Hoja {{ $complaint->code }}</h2>
    <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary rounded-0 text-uppercase small tracking-wider px-3">&larr; Volver</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
            @include('shop.complaints.partials.sheet', ['complaint' => $complaint])
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-0 bg-white p-4">
            <h5 class="fw-bold text-uppercase tracking-wider small border-bottom pb-2 mb-3">Respuesta del proveedor</h5>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small rounded-0">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
                @csrf
                @method('PUT')
                <label for="response" class="form-label small text-muted">Observaciones y acciones adoptadas</label>
                <textarea id="response" name="response" class="form-control rounded-0 mb-3" rows="8" required>{{ old('response', $complaint->response) }}</textarea>
                <button type="submit" class="btn btn-dark rounded-0 w-100 text-uppercase tracking-wider small fw-bold">
                    {{ $complaint->response ? 'Actualizar respuesta' : 'Registrar respuesta' }}
                </button>
            </form>

            <p class="small text-muted mt-3 mb-0">
                El consumidor ve la respuesta en su constancia en línea. Envíasela también por correo a
                <a href="mailto:{{ $complaint->email }}?subject={{ rawurlencode('Respuesta a tu hoja de reclamación ' . $complaint->code) }}">{{ $complaint->email }}</a>.
            </p>
        </div>
    </div>
</div>
@endsection
