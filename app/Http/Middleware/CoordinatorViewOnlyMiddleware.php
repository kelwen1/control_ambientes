<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoordinatorViewOnlyMiddleware
{
    /**
     * El coordinador solo tiene permisos de visualización y búsqueda.
     * No puede crear, editar, eliminar ni exportar en Fichas, Ambientes/Reservas ni Inventario.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Solo aplicamos restricción al rol Coordinador (id_rol = 2)
        if (!$user->isCoordinator()) {
            return $next($request);
        }

        // El coordinador intentó acceder a una ruta de escritura/exportación
        return redirect()
            ->route('dashboard')
            ->with('error', 'No tienes permisos para realizar esta acción. Solo puedes visualizar y buscar.');
    }
}
