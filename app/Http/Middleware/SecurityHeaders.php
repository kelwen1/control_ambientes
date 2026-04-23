<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->environment('local', 'testing')) {
            // En local, Vite (HMR) carga JS desde :5173; una CSP fija bloquea el panel y el login.
            // En producción se aplica la CSP debajo.
            return $response;
        }

        // Prevenir que el navegador interprete content-type incorrectamente (ej: MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Evitar que la app se cargue en iframes de otros sitios (clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Activación del filtro XSS del navegador (legacy, CSP es más moderno)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content-Security-Policy: limita orígenes de scripts, estilos, imágenes, etc. para mitigar XSS.
        // Incluye 'unsafe-inline' porque Laravel/Blade usa scripts inline.
        // Tailwind se sirve con Vite (public/build) — sin CDN. Google Fonts: link + fuentes en gstatic.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS: solo en producción y fuera de localhost. Obliga al navegador a usar HTTPS
        // durante 1 año, reduciendo ataques de downgrade.
        $host = $request->getHost();
        $isLocal = in_array($host, ['localhost', '127.0.0.1'], true) || str_starts_with($host, '127.');
        if (config('app.env') === 'production' && ! $isLocal) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
