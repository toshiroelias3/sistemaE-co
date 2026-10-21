<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar si el usuario está logueado y si es ADMIN
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Permite el paso a la ruta
        }

        // 2. Si no es admin, denegar acceso y redirigir con mensaje de error
        return redirect('/')->with('error', 'Acceso denegado. No tienes permisos de administrador.');
    }
}