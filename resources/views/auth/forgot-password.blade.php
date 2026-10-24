@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card border border-dark p-4 bg-white">
            <div class="text-center mb-4">
                <h1 class="h2 text-dark m-0">Recuperar contraseña</h1>
                <p class="text-muted small mt-2 mb-0">Escribe el correo de tu cuenta y te enviaremos un enlace para crear una nueva.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label small fw-medium text-muted">Correo electrónico</label>
                    <input type="email" class="form-control border-dark-subtle" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 small fw-bold">Enviar enlace</button>
            </form>

            <div class="text-center mt-4 small">
                <a href="{{ route('login') }}" class="text-dark fw-bold text-decoration-none">Volver a iniciar sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection
