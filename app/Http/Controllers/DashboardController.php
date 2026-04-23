<?php

namespace App\Http\Controllers;

use App\Helpers\SesionesHelper;
use App\Models\Ambiente;
use App\Models\Reserva;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    /** Desplazamiento desde el lunes: lunes=0 … domingo=6 */
    private const OFFSET_DIA_DESDE_LUNES = [
        'lunes' => 0,
        'martes' => 1,
        'miercoles' => 2,
        'jueves' => 3,
        'viernes' => 4,
        'sabado' => 5,
        'domingo' => 6,
    ];

    /**
     * Display the dashboard.
     */
    public function index(Request $request)
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

        // Contar usuarios (solo relevante para administradores; no se consulta para otros roles)
        $usuariosActivos = auth()->user()->isAdmin() ? User::count() : 0;

        // Contar fichas desde la tabla fichas
        $totalFichas = DB::table('ficha')->count();

        // Fichas activas: fecha_fin >= '2026-01-01' o fecha_fin es NULL (aún no ha terminado)
        $fichasActivas = DB::table('ficha')
            ->where(function ($query) {
                $query->where('fecha_fin', '>=', '2026-01-01')
                    ->orWhereNull('fecha_fin');
            })
            ->count();

        // Fichas inactivas: fecha_fin < '2026-01-01' (terminaron antes de 2026, hasta 31/12/2025)
        $fichasInactivas = DB::table('ficha')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', '2026-01-01')
            ->count();

        $esInstructor = auth()->user()->isInstructor();
        $diasSemanaInstructor = self::DIAS_SEMANA;
        $reservasInstructorPorDia = [];
        $semanaReferenciaLunes = null;
        $semanaEtiqueta = null;
        $semanaAnterior = null;
        $semanaSiguiente = null;

        if ($esInstructor) {
            $idPersona = Auth::user()->persona->id_persona ?? null;
            if ($idPersona) {
                try {
                    $semanaReferenciaLunes = $request->filled('semana')
                        ? Carbon::parse($request->get('semana'))->startOfWeek(Carbon::MONDAY)
                        : now()->startOfWeek(Carbon::MONDAY);
                } catch (\Throwable $e) {
                    $semanaReferenciaLunes = now()->startOfWeek(Carbon::MONDAY);
                }

                $finSemana = $semanaReferenciaLunes->copy()->endOfWeek(Carbon::SUNDAY);
                $semanaEtiqueta = $semanaReferenciaLunes->format('d/m/Y').' – '.$finSemana->format('d/m/Y');
                $semanaAnterior = $semanaReferenciaLunes->copy()->subWeek()->format('Y-m-d');
                $semanaSiguiente = $semanaReferenciaLunes->copy()->addWeek()->format('Y-m-d');

                $reservas = Reserva::with(['ambiente', 'ficha.programa'])
                    ->where('reservas.id_persona', $idPersona)
                    ->where('reservas.id_estado_reserva', 1)
                    ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
                    ->leftJoin('resultados', 'reservas.id_resultado', '=', 'resultados.id_resultado')
                    ->whereIn('dia_semana.dia_semana', self::DIAS_SEMANA)
                    ->orderBy('reservas.id_reserva')
                    ->get(['reservas.*', DB::raw('dia_semana.dia_semana as dia_semana_text'), DB::raw('resultados.denominacion as resultado_denominacion')]);

                $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];

                $libPorReserva = $this->mapaDiasLiberadosPorReserva($reservas->pluck('id_reserva')->all());

                foreach (self::DIAS_SEMANA as $dia) {
                    $offset = self::OFFSET_DIA_DESDE_LUNES[$dia] ?? 0;
                    $fechaColumna = $semanaReferenciaLunes->copy()->addDays($offset)->format('Y-m-d');

                    $delDia = $reservas->filter(function ($r) use ($dia, $fechaColumna, $diasMap, $libPorReserva) {
                        if ($r->dia_semana_text !== $dia) {
                            return false;
                        }
                        $obj = (object) [
                            'id_reserva' => $r->id_reserva,
                            'fecha_inicio' => $r->fecha_inicio,
                            'fecha_fin' => $r->fecha_fin,
                            'dia_semana' => $r->dia_semana_text,
                        ];
                        $fechas = SesionesHelper::fechasClaseReserva($obj, $diasMap);
                        $lib = $libPorReserva[(int) $r->id_reserva] ?? [];
                        foreach ($fechas as $ymd) {
                            if ($ymd === $fechaColumna && ! isset($lib[$ymd])) {
                                return true;
                            }
                        }

                        return false;
                    });

                    $reservasInstructorPorDia[$dia] = $this->agruparReservasMismaAsignacion($delDia);
                }
            }
        }

        return view('dashboard', [
            'totalAmbientes' => $totalAmbientes,
            'ambientesDisponibles' => $ambientesDisponibles,
            'ambientesOcupados' => $ambientesOcupados,
            'ambientesMantenimiento' => $ambientesMantenimiento,
            'usuariosActivos' => $usuariosActivos,
            'totalFichas' => $totalFichas,
            'fichasActivas' => $fichasActivas,
            'fichasInactivas' => $fichasInactivas,
            'esInstructor' => $esInstructor,
            'diasSemanaInstructor' => $diasSemanaInstructor,
            'reservasInstructorPorDia' => $reservasInstructorPorDia,
            'semanaReferenciaLunes' => $semanaReferenciaLunes,
            'semanaEtiqueta' => $semanaEtiqueta,
            'semanaAnterior' => $semanaAnterior,
            'semanaSiguiente' => $semanaSiguiente,
        ]);
    }

    /**
     * @param  array<int>  $idsReserva
     * @return array<int, array<string, true>>
     */
    private function mapaDiasLiberadosPorReserva(array $idsReserva): array
    {
        if ($idsReserva === []) {
            return [];
        }
        $rows = DB::table('reserva_dias_liberados')
            ->whereIn('id_reserva', $idsReserva)
            ->get(['id_reserva', 'fecha']);
        $map = [];
        foreach ($rows as $row) {
            $f = $row->fecha;
            $ymd = $f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f;
            $id = (int) $row->id_reserva;
            $map[$id][$ymd] = true;
        }

        return $map;
    }

    /**
     * Una sola tarjeta por combinación ficha + ambiente + jornada + resultado + competencia.
     *
     * @param  Collection<int, Reserva>  $items
     * @return Collection<int, Reserva>
     */
    private function agruparReservasMismaAsignacion(Collection $items): Collection
    {
        return $items
            ->groupBy(function ($r) {
                return implode('|', [
                    (int) $r->id_ficha,
                    (int) $r->id_ambiente,
                    (int) ($r->id_jornada ?? 0),
                    (int) ($r->id_resultado ?? 0),
                    (int) ($r->id_competencia ?? 0),
                ]);
            })
            ->map(function (Collection $group) {
                $first = $group->sortBy('id_reserva')->first();
                $n = $group->count();
                $sesiones = $first->id_resultado
                    ? SesionesHelper::calcularSesionesFichaResultado((int) $first->id_ficha, (int) $first->id_resultado)
                    : SesionesHelper::calcularSesionesFichaCompetencia((int) $first->id_ficha, (int) $first->id_competencia);
                $first->sesiones_total = $sesiones['total'];
                $first->sesiones_consumidas = $sesiones['consumidas'];
                $first->sesiones_restantes = $sesiones['restantes'];
                $first->clases_agrupadas_en_celda = $n;

                return $first;
            })
            ->values();
    }
}
