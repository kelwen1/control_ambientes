<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AmbientesController extends Controller
{
    /**
     * Rangos de hora por jornada (mañana, tarde, noche). Sin solapamiento para permitir
     * hasta 3 reservas el mismo día: mañana 7-13, tarde 13-19, noche 19-22.
     * Sábados y domingos: una sola jornada 7-17 (una reserva por ambiente por día).
     * Se usan con segundos (HH:MM:SS) para comparación correcta con TIME en MySQL.
     */
    private const JORNADAS = [
        'manana' => ['inicio' => '07:00:00', 'fin' => '13:00:00'],
        'tarde'  => ['inicio' => '13:00:00', 'fin' => '19:00:00'],
        'noche'  => ['inicio' => '19:00:00', 'fin' => '22:00:00'],
        'fin_semana' => ['inicio' => '07:00:00', 'fin' => '17:00:00'],
    ];

    /**
     * Disponibilidad por jornada: ambientes libres en un tipo de día y jornada.
     * Solo se consideran reservas ACTIVAS.
     * GET /ambientes/disponibilidad?dia_tipo=lunes_viernes|sabado|domingo&jornada=manana|tarde|noche|fin_semana
     * Para sábado/domingo solo se usa jornada fin_semana (7-17).
     */
    public function disponibilidad(Request $request)
    {
        $diaTipo = $request->get('dia_tipo');
        $jornada = $request->get('jornada');

        $ambientesDisponibles = collect();
        $mensaje = null;

        // Sábado y domingo solo admiten jornada fin_semana
        $esFinDeSemana = in_array($diaTipo, ['sabado', 'domingo'], true);
        if ($esFinDeSemana) {
            $jornada = 'fin_semana';
        }

        if ($diaTipo && $jornada && isset(self::JORNADAS[$jornada])) {
            $diaSemana = in_array($diaTipo, ['sabado', 'domingo'], true) ? $diaTipo : 'lunes';
            $rango = self::JORNADAS[$jornada];
            $inicioJornada = $rango['inicio'];
            $finJornada = $rango['fin'];

            // Ambientes con alguna reserva ACTIVA en ese día con horario que solapa la jornada.
            // Solapamiento: [hora_inicio, hora_fin] con [inicioJornada, finJornada]
            $ocupados = DB::table('reservas')
                ->where('reservas.id_estado_reserva', 1)
                ->whereRaw('LOWER(TRIM(reservas.dia_semana)) = ?', [strtolower($diaSemana)])
                ->whereRaw('reservas.hora_inicio < ? AND reservas.hora_fin > ?', [$finJornada, $inicioJornada])
                ->pluck('reservas.id_ambiente')
                ->unique()
                ->values();

            // Ambientes que no están ocupados en esa jornada/día (incluye mantenimiento,
            // que se marcará aparte en la vista)
            $ambientesDisponibles = DB::table('ambientes')
                ->select('ambientes.id_ambiente', 'ambientes.num_ambiente', 'ambientes.id_estado', 'ambientes.capacidad_max')
                ->whereNotIn('ambientes.id_ambiente', $ocupados->isEmpty() ? [-1] : $ocupados)
                ->orderByRaw('CAST(ambientes.num_ambiente AS UNSIGNED), ambientes.num_ambiente')
                ->get();

            $mensaje = $ambientesDisponibles->isEmpty()
                ? 'No hay ambientes disponibles en la jornada seleccionada.'
                : null;
        }

        return view('ambientes.disponibilidad', [
            'ambientes' => $ambientesDisponibles,
            'dia_tipo' => $diaTipo,
            'jornada' => $jornada,
            'mensaje' => $mensaje,
            'jornadas' => self::JORNADAS,
        ]);
    }
    /**
     * Display a listing of the ambientes with their reservations.
     */
    public function index(Request $request)
    {
        // Obtener todas las reservas con información de ambientes y fichas
        $query = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->select(
                'reservas.id_reserva',
                'reservas.id_ambiente',
                'reservas.id_ficha',
                'reservas.dia_semana',
                'reservas.hora_inicio',
                'reservas.hora_fin',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'reservas.id_estado_reserva',
                'reservas.observaciones',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado'
            )
            ->orderBy('reservas.id_reserva', 'desc');

        // Búsqueda solo por número de ambiente
        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('ambientes.num_ambiente', 'like', '%' . $search . '%');
        }

        $reservas = $query->paginate(10);

        return view('ambientes.index', [
            'reservas' => $reservas,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Export reservas a PDF
     */
    public function export(Request $request)
    {
        $query = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->select(
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado',
                'reservas.dia_semana',
                'reservas.hora_inicio',
                'reservas.hora_fin',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'reservas.observaciones'
            )
            ->orderBy('reservas.id_reserva', 'desc');

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('ambientes.num_ambiente', 'like', '%' . $search . '%');
        }

        $reservas = $query->get();
        $filename = 'ambientes_reservas_' . date('Y-m-d_His') . '.pdf';
        return app('dompdf.wrapper')->loadView('pdf.ambientes', compact('reservas'))
            ->download($filename);
    }
}

