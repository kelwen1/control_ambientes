<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\AvanceFicha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    /** Días de la semana laboral (tablero L-V). */
    private const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    /**
     * Tablero del instructor: reservas agrupadas por día (L-V).
     * Solo instructores.
     */
    public function tablero()
    {
        $user = Auth::user();
        if (!$user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (!$idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $reservas = Reserva::with(['ambiente', 'ficha.programa'])
            ->where('id_persona', $idPersona)
            ->where('id_estado_reserva', 1)
            ->where(function ($q) {
                $q->whereIn('dia_semana', self::DIAS_SEMANA)
                  ->orWhere('dia_semana', 'lunes'); // "lunes" = repetido L-V en el sistema actual
            })
            ->orderBy('hora_inicio')
            ->get();

        $porDia = [];
        foreach (self::DIAS_SEMANA as $dia) {
            $porDia[$dia] = $reservas->filter(function ($r) use ($dia) {
                return $r->dia_semana === $dia || $r->dia_semana === 'lunes';
            })->values();
        }

        return view('instructor.tablero', [
            'porDia' => $porDia,
            'diasSemana' => self::DIAS_SEMANA,
        ]);
    }

    /**
     * Detalle de un día: todas las reservas del instructor ese día con info completa
     * (programa, ficha, hasta cuándo, competencia, resultado, sección).
     */
    public function detalleDia(string $dia)
    {
        $user = Auth::user();
        if (!$user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        if (!in_array($dia, self::DIAS_SEMANA, true)) {
            return redirect()->route('instructor.tablero')->with('error', 'Día no válido.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (!$idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $reservas = Reserva::with([
            'ambiente',
            'ficha.programa',
            'ficha.avanceActual.competencia',
            'ficha.avanceActual.resultado',
        ])
            ->where('id_persona', $idPersona)
            ->where('id_estado_reserva', 1)
            ->where(function ($q) use ($dia) {
                $q->where('dia_semana', $dia)->orWhere('dia_semana', 'lunes');
            })
            ->orderBy('hora_inicio')
            ->get();

        $labelsDia = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
        ];

        return view('instructor.detalle-dia', [
            'dia' => $dia,
            'diaLabel' => $labelsDia[$dia] ?? $dia,
            'reservas' => $reservas,
        ]);
    }
}
