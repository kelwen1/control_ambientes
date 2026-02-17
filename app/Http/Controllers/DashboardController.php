<?php

namespace App\Http\Controllers;

use App\Models\Ambiente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Contar el total de registros en la tabla ambientes
        $totalAmbientes = Ambiente::count();
        
        // Contar ambientes ocupados (id_estado = 3 según la tabla estd_ambte: 1=Disponible, 2=Mantenimiento, 3=Ocupado)
        $ambientesOcupados = DB::table('ambientes')
            ->where('id_estado', 3)
            ->count();
        
        // Contar ambientes en mantenimiento (id_estado = 2)
        $ambientesMantenimiento = DB::table('ambientes')
            ->where('id_estado', 2)
            ->count();
        
        // Calcular ambientes disponibles (total - ocupados - mantenimiento)
        $ambientesDisponibles = max(0, $totalAmbientes - $ambientesOcupados - $ambientesMantenimiento);
        
        // Contar usuarios activos desde la tabla users
        $usuariosActivos = User::count();
        
        // Contar fichas desde la tabla fichas
        $totalFichas = DB::table('ficha')->count();
        
        // Fichas activas: fecha_fin >= '2026-01-01' o fecha_fin es NULL (aún no ha terminado)
        $fichasActivas = DB::table('ficha')
            ->where(function($query) {
                $query->where('fecha_fin', '>=', '2026-01-01')
                      ->orWhereNull('fecha_fin');
            })
            ->count();
        
        // Fichas inactivas: fecha_fin < '2026-01-01' (terminaron antes de 2026, hasta 31/12/2025)
        $fichasInactivas = DB::table('ficha')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', '2026-01-01')
            ->count();
        
        return view('dashboard', [
            'totalAmbientes' => $totalAmbientes,
            'ambientesDisponibles' => $ambientesDisponibles,
            'ambientesOcupados' => $ambientesOcupados,
            'ambientesMantenimiento' => $ambientesMantenimiento,
            'usuariosActivos' => $usuariosActivos,
            'totalFichas' => $totalFichas,
            'fichasActivas' => $fichasActivas,
            'fichasInactivas' => $fichasInactivas
        ]);
    }
}

