<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoordinatorViewOnlyMiddleware
{
    /**
     * Coordinador normal (id_rol=3): consulta, reportes Excel y puede crear reservas (asignación).
     * No crea/edita/elimina fichas ni edita/elimina reservas; catálogos solo lectura (listados).
     * La administración de usuarios es solo administrador (superusuario).
     * Coordinacion_L (id_rol=2) sí puede crear/editar/eliminar en lo permitido por rutas.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Solo restringir a coordinacion (3). Coordinacion_L (2) puede crear/editar/eliminar
        if (! $user->isCoordinatorOnly()) {
            return $next($request);
        }

        if ($request->routeIs('reservas.create', 'reservas.store')) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'No tienes permisos para realizar esta acción.');
    }
}
