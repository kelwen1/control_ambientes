<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstructorOnlyMiddleware
{
    /**
     * Solo instructores: calendario personal, detalle por día y rutas de reporte PDF propio.
     * Coordinación y administración usan otros módulos (consulta / reportes globales).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isInstructor()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Esta sección es exclusiva para instructores.');
        }

        return $next($request);
    }
}
