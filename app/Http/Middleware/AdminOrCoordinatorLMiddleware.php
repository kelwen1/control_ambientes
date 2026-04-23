<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrCoordinatorLMiddleware
{
    /**
     * Permite acceso a administrador (1) o coordinacion_L (2).
     * Coordinador normal (3), instructor (4) y otros: no (usar catalog.access para lectura de catálogos del coord. normal).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->canManageCatalog()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
