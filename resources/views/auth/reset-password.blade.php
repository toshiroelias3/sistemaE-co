@extends('layouts.app')

@section('noindex', '1')

@section('title', 'Nueva contraseña')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card border border-dark p-4 bg-white">
            <div class="text-center mb-4">
                <h1 class="h2 text-dark m-0">Crea una nueva contraseña</h1>
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

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label small fw-medium text-muted">Correo electrónico</label>
                    <input type="email" class="form-control border-dark-subtle" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label small fw-medium text-muted">Nueva contraseña (mínimo 8 caracteres, con letras y números)</label>
                    <input type="password" class="form-control border-dark-subtle" id="password" name="password" required autocomplete="new-password" autofocus>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small fw-medium text-muted">Repite la nueva contraseña</label>
                    <input type="password" class="form-control border-dark-subtle" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-dark w-100 py-2 small fw-bold">Guardar contraseña</button>
            </form>
        </div>
    </div>
</div>
@endsection
