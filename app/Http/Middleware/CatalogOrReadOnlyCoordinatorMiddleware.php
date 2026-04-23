<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catálogos (ambientes, programas, niveles, competencias, resultados):
 * administrador y coordinador L: acceso completo;
 * coordinador normal (solo lectura): solo GET a los listados index.
 */
class CatalogOrReadOnlyCoordinatorMiddleware
{
    /** @var list<string> */
    private const READ_ONLY_ROUTE_NAMES = [
        'ambientes.gestion.index',
        'niveles-programa.index',
        'programas.index',
        'competencias.index',
        'resultados.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->isAdmin() || $user->isCoordinatorL()) {
            return $next($request);
        }

        if ($user->isCoordinatorOnly()) {
            if ($request->isMethod('GET') && $request->routeIs(self::READ_ONLY_ROUTE_NAMES)) {
                return $next($request);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'No tienes permisos para realizar esta acción. Solo puedes consultar estos listados.');
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'No tienes permisos para acceder a esta sección.');
    }
}
