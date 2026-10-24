@extends('layouts.app')

@section('title', 'Libro de Reclamaciones')
@section('meta_description', 'Libro de Reclamaciones virtual de TEZAN. Registra un reclamo o una queja sobre nuestros productos o atención.')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <h1 class="tz-display fs-1 mb-2">Libro de Reclamaciones</h1>
        <p class="text-muted mb-4">
            Conforme al Código de Protección y Defensa del Consumidor, contamos con un Libro de Reclamaciones virtual.
            Te enviaremos una copia de tu hoja al correo que indiques y responderemos en un plazo no mayor a {{ \App\Models\Complaint::RESPONSE_DAYS }} días hábiles.
        </p>

        <div class="p-3 mb-4" style="background: var(--tz-warm);">
            <p class="tz-label mb-2">Proveedor</p>
            <p class="mb-0 small">
                {{ config('tienda.razon_social') }}
                @if(config('tienda.ruc')), RUC {{ config('tienda.ruc') }}@endif
                @if(config('tienda.direccion')), {{ config('tienda.direccion') }}@endif
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('complaints.store') }}" method="POST" novalidate>
            @csrf

            <h2 class="tz-label mt-4 mb-3">1. Tus datos</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label for="consumer_name" class="form-label small">Nombre completo *</label>
                    <input type="text" id="consumer_name" name="consumer_name" class="form-control" value="{{ old('consumer_name', Auth::user()->name ?? '') }}" required autocomplete="name">
                </div>
                <div class="col-5 col-md-3">
                    <label for="document_type" class="form-label small">Documento *</label>
                    <select id="document_type" name="document_type" class="form-select" required>
                        @foreach(\App\Models\Complaint::DOCUMENT_TYPES as $doc)
                            <option value="{{ $doc }}" @selected(old('document_type') === $doc)>{{ $doc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-7 col-md-4">
                    <label for="document_number" class="form-label small">N.° de documento *</label>
                    <input type="text" id="document_number" name="document_number" class="form-control" value="{{ old('document_number') }}" required inputmode="numeric">
                </div>
                <div class="col-12 col-md-5">
                    <label for="phone" class="form-label small">Teléfono *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required autocomplete="tel">
                </div>
                <div class="col-12 col-md-7">
                    <label for="address" class="form-label small">Domicilio *</label>
                    <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" required autocomplete="street-address">
                </div>
                <div class="col-12 col-md-5">
                    <label for="email" class="form-label small">Correo electrónico *</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', Auth::user()->email ?? '') }}" required autocomplete="email">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_minor" value="0">
                        <input class="form-check-input" type="checkbox" id="is_minor" name="is_minor" value="1" @checked(old('is_minor'))>
                        <label class="form-check-label small" for="is_minor">Soy menor de edad</label>
                    </div>
                </div>
                <div class="col-12 {{ old('is_minor') ? '' : 'd-none' }}" id="guardian-field">
                    <label for="guardian_name" class="form-label small">Nombre del padre, madre o apoderado *</label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
                </div>
            </div>

            <h2 class="tz-label mt-5 mb-3">2. Producto o servicio</h2>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="item_type" class="form-label small">Tipo *</label>
                    <select id="item_type" name="item_type" class="form-select" required>
                        @foreach(\App\Models\Complaint::ITEM_TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('item_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-7 col-md-4">
                    <label for="order_reference" class="form-label small">N.° de pedido</label>
                    <input type="text" id="order_reference" name="order_reference" class="form-control" value="{{ old('order_reference') }}" placeholder="TZN-...">
                </div>
                <div class="col-5 col-md-4">
                    <label for="amount" class="form-label small">Monto (S/)</label>
                    <input type="number" id="amount" name="amount" class="form-control" value="{{ old('amount') }}" min="0" step="0.01">
                </div>
                <div class="col-12">
                    <label for="item_description" class="form-label small">Descripción *</label>
                    <input type="text" id="item_description" name="item_description" class="form-control" value="{{ old('item_description') }}" required placeholder="Ej.: Polo Oversize talla M, color beige">
                </div>
            </div>

            <h2 class="tz-label mt-5 mb-3">3. Detalle</h2>
            <fieldset class="mb-3">
                <legend class="form-label small">Tipo *</legend>
                @foreach(\App\Models\Complaint::TYPES as $value => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="type_{{ $value }}" value="{{ $value }}" @checked(old('type', 'reclamo') === $value) required>
                        <label class="form-check-label small" for="type_{{ $value }}">
                            <strong>{{ $label }}:</strong>
                            {{ $value === 'reclamo' ? 'disconformidad relacionada con los productos o servicios.' : 'disconformidad no relacionada con los productos o servicios, o malestar respecto a la atención al público.' }}
                        </label>
                    </div>
                @endforeach
            </fieldset>
            <div class="mb-3">
                <label for="detail" class="form-label small">¿Qué pasó? *</label>
                <textarea id="detail" name="detail" class="form-control" rows="5" required maxlength="3000">{{ old('detail') }}</textarea>
            </div>
            <div class="mb-4">
                <label for="consumer_request" class="form-label small">¿Qué solicitas? *</label>
                <textarea id="consumer_request" name="consumer_request" class="form-control" rows="3" required maxlength="2000">{{ old('consumer_request') }}</textarea>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="accepted" name="accepted" value="1" required>
                <label class="form-check-label small" for="accepted">Declaro que los datos consignados son correctos y autorizo el uso de mis datos para atender esta reclamación.</label>
            </div>

            <button type="submit" class="btn btn-dark px-5" onclick="setTimeout(() => this.disabled = true, 0)">Enviar hoja de reclamación</button>

            <p class="small text-muted mt-4 mb-0">
                La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.
            </p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('is_minor').addEventListener('change', function () {
        document.getElementById('guardian-field').classList.toggle('d-none', !this.checked);
    });
</script>
@endpush
