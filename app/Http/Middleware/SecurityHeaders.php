<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas (OWASP Secure Headers).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS solo cuando la tienda ya se sirve por https
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP: solo recursos propios y los proveedores que la tienda usa. Se omite mientras corre
        // el servidor de desarrollo de Vite (npm run dev), que sirve los assets desde otro puerto.
        if (!(app()->environment('local') && file_exists(public_path('hot')))) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                // Las vistas usan scripts en línea pequeños; se permiten, pero no scripts de otros dominios
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
                "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com",
                "img-src 'self' data: https://images.unsplash.com",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]));
        }

        return $response;
    }
}
