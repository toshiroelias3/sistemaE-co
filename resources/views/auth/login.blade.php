@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card border border-dark p-4 bg-white">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark m-0">Ingresar</h2>
                <p class="text-muted small">Accede a tu cuenta de TEZAN</p>
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

            <form action="{{ route('login') }}" method="POST">
                @csrf <div class="mb-3">
                    <label for="email" class="form-label small fw-medium text-muted">Correo electrónico</label>
                    <input type="email" class="form-control border-dark-subtle" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-medium text-muted">Contraseña</label>
                    <input type="password" class="form-control border-dark-subtle" id="password" name="password" required>
                </div>

                <div class="mb-4 d-flex justify-content-between align-items-center small">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted" for="remember">Recordarme</label>
                    </div>

                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 small fw-bold">Iniciar sesión</button>
            </form>

            <div class="text-center mt-4 small">
                <span class="text-muted">¿No tienes una cuenta?</span>
                <a href="{{ route('register') }}" class="text-dark fw-bold text-decoration-none ms-1">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
@endsection