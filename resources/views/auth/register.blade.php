@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Crear cuenta')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card border border-dark p-4 bg-white">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark m-0">Registro</h2>
                <p class="text-muted small">Únete a nuestra comunidad exclusiva</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label small fw-medium text-muted">Nombre completo</label>
                    <input type="text" class="form-control border-dark-subtle" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-medium text-muted">Correo electrónico</label>
                    <input type="email" class="form-control border-dark-subtle" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-medium text-muted">Contraseña (mínimo 8 caracteres, con letras y números)</label>
                    <input type="password" class="form-control border-dark-subtle" id="password" name="password" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small fw-medium text-muted">Confirmar contraseña</label>
                    <input type="password" class="form-control border-dark-subtle" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 small fw-bold">Crear cuenta</button>
                <p class="small text-muted mt-3 mb-0">
                    Al crear tu cuenta aceptas los <a href="{{ route('pages.terms') }}" class="link-dark">términos y condiciones</a>
                    y la <a href="{{ route('pages.privacy') }}" class="link-dark">política de privacidad</a>.
                </p>
            </form>

            <div class="text-center mt-4 small">
                <span class="text-muted">¿Ya tienes una cuenta?</span>
                <a href="{{ route('login') }}" class="text-dark fw-bold text-decoration-none ms-1">Inicia sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection