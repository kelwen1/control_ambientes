<?php

namespace App\Http\Controllers;

use App\Helpers\FestivosColombiaHelper;
use App\Helpers\SesionesHelper;
use App\Models\Reserva;
use App\Models\ReservaDiaLiberado;
use App\Support\SecureRedirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    /** Días de la semana (L-D) para el tablero del instructor. */
    private const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    /**
     * Tablero del instructor: redirige al dashboard (el calendario semanal
     * es ahora el contenido principal del inicio para instructores).
     */
    public function tablero()
    {
        return redirect()->route('dashboard');
    }

    /**
     * Detalle de un día: todas las reservas del instructor ese día con info completa
     * (programa, ficha, hasta cuándo, competencia, resultado, sección).
     */
    public function detalleDia(string $dia)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        if (! in_array($dia, self::DIAS_SEMANA, true)) {
            return redirect()->route('instructor.tablero')->with('error', 'Día no válido.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $reservas = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('programa', 'ficha.id_programa', '=', 'programa.id_programa')
            ->leftJoin('competencia', 'reservas.id_competencia', '=', 'competencia.id_competencia')
            ->leftJoin('resultados', 'reservas.id_resultado', '=', 'resultados.id_resultado')
            ->leftJoin('persona', 'reservas.id_persona', '=', 'persona.id_persona')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_estado_reserva', 1)
            ->where('dia_semana.dia_semana', $dia)
            ->orderBy('reservas.id_reserva')
            ->select(
                'reservas.id_reserva',
                'reservas.id_ficha',
                'reservas.id_competencia',
                'reservas.id_resultado',
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'ficha.fecha_fin as ficha_fecha_fin',
                'programa.nombre_programa',
                'competencia.nombre_competencia',
                'resultados.denominacion as resultado_denominacion',
                'persona.nombres as instructor_nombres',
                'persona.apellidos as instructor_apellidos',
                DB::raw('dia_semana.dia_semana as dia_semana_text')
            )
            ->get();

        // Cargar días liberados, sesiones restantes, fechas próximas y festivos por reserva
        $diasLiberadosPorReserva = [];
        $proximasFechasPorReserva = [];
        $sesionesPorReserva = [];
        $festivosPorReserva = [];
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        foreach ($reservas as $r) {
            $targetDow = $diasMap[$dia] ?? -1;
            $festivosPorReserva[$r->id_reserva] = FestivosColombiaHelper::festivosEnRango(
                $r->fecha_inicio,
                $r->fecha_fin,
                $targetDow
            );
            $sesionesPorReserva[$r->id_reserva] = $r->id_resultado
                ? SesionesHelper::calcularSesionesFichaResultado((int) $r->id_ficha, (int) $r->id_resultado)
                : SesionesHelper::calcularSesionesFichaCompetencia((int) $r->id_ficha, (int) $r->id_competencia);
            $diasLiberadosPorReserva[$r->id_reserva] = DB::table('reserva_dias_liberados')
                ->where('id_reserva', $r->id_reserva)
                ->orderBy('fecha')
                ->get()
                ->map(fn ($row) => ['valor' => $row->fecha, 'label' => \Carbon\Carbon::parse($row->fecha)->format('d/m/Y')])
                ->toArray();

            // Fechas del día de la semana dentro del rango de la reserva (inclusivo; próximas 12)
            $inicio = \Carbon\Carbon::parse($r->fecha_inicio)->startOfDay();
            $fin = \Carbon\Carbon::parse($r->fecha_fin)->startOfDay();
            $hoy = now()->startOfDay();
            $fechas = [];
            $f = $inicio->copy();
            while ($f->lte($fin) && count($fechas) < 12) {
                if ($f->dayOfWeek === $targetDow && $f->gte($hoy)) {
                    $fechas[] = ['valor' => $f->format('Y-m-d'), 'label' => $f->format('d/m/Y')];
                }
                $f->addDay();
            }
            $proximasFechasPorReserva[$r->id_reserva] = $fechas;
        }

        $labelsDia = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        return view('instructor.detalle-dia', [
            'dia' => $dia,
            'diaLabel' => $labelsDia[$dia] ?? $dia,
            'reservas' => $reservas,
            'diasLiberadosPorReserva' => $diasLiberadosPorReserva,
            'proximasFechasPorReserva' => $proximasFechasPorReserva,
            'sesionesPorReserva' => $sesionesPorReserva,
            'festivosPorReserva' => $festivosPorReserva,
        ]);
    }

    /**
     * Reporte de reservas del instructor: vista con tabla (sin IDs).
     */
    public function reporteReservas()
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $reservas = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('programa', 'ficha.id_programa', '=', 'programa.id_programa')
            ->leftJoin('competencia', 'reservas.id_competencia', '=', 'competencia.id_competencia')
            ->leftJoin('resultados', 'reservas.id_resultado', '=', 'resultados.id_resultado')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_estado_reserva', 1)
            ->orderBy('reservas.fecha_inicio')
            ->orderBy('dia_semana.id_dia_semana')
            ->orderBy('ficha.id_jornada')
            ->select(
                'reservas.id_reserva',
                'reservas.id_ficha',
                'reservas.id_competencia',
                'reservas.id_resultado',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'programa.nombre_programa',
                'competencia.nombre_competencia',
                'resultados.denominacion as resultado_denominacion',
                'estado_reserva.nombre_estado',
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin'
            )
            ->get();

        $sesionesPorReserva = [];
        foreach ($reservas as $r) {
            $sesionesPorReserva[$r->id_reserva] = $r->id_resultado
                ? SesionesHelper::calcularSesionesFichaResultado((int) $r->id_ficha, (int) $r->id_resultado)
                : SesionesHelper::calcularSesionesFichaCompetencia((int) $r->id_ficha, (int) $r->id_competencia);
        }

        return view('instructor.reporte-reservas', [
            'reservas' => $reservas,
            'sesionesPorReserva' => $sesionesPorReserva,
        ]);
    }

    /**
     * Exportar reporte de reservas del instructor a PDF (listado completo activas).
     */
    public function exportReservas()
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $reservas = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('programa', 'ficha.id_programa', '=', 'programa.id_programa')
            ->leftJoin('competencia', 'reservas.id_competencia', '=', 'competencia.id_competencia')
            ->leftJoin('resultados', 'reservas.id_resultado', '=', 'resultados.id_resultado')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_estado_reserva', 1)
            ->orderBy('reservas.fecha_inicio')
            ->orderBy('dia_semana.id_dia_semana')
            ->orderBy('ficha.id_jornada')
            ->select(
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'programa.nombre_programa',
                'competencia.nombre_competencia',
                'resultados.denominacion as resultado_denominacion',
                'estado_reserva.nombre_estado',
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin'
            )
            ->get();

        $filename = 'mis_reservas_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.instructor-reservas', compact('reservas'))
            ->download($filename);
    }

    /**
     * Elegir año, mes (opcional) y semana del mes (opcional) antes de descargar el PDF por fechas de sesión.
     */
    public function reporteReservasFiltro(Request $request)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

        $anioSel = (int) $request->get('anio', now()->year);
        if ($anioSel < 2000 || $anioSel > 2100) {
            $anioSel = (int) now()->year;
        }
        $mesSel = $request->filled('mes') ? (int) $request->mes : null;
        if ($mesSel !== null && ($mesSel < 1 || $mesSel > 12)) {
            $mesSel = null;
        }
        $semanasDelMes = ($mesSel !== null) ? $this->semanasEnMes($anioSel, $mesSel) : [];
        $aniosLista = range((int) now()->year + 1, (int) now()->year - 5);

        return view('instructor.reporte-filtro', [
            'anioSel' => $anioSel,
            'mesSel' => $mesSel,
            'semanasDelMes' => $semanasDelMes,
            'aniosLista' => $aniosLista,
        ]);
    }

    /**
     * PDF de sesiones del instructor en un rango: año completo, mes completo o una semana dentro del mes.
     */
    public function exportReservasFiltrado(Request $request)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $anio = (int) $request->input('anio', 0);
        if ($anio < 2000 || $anio > 2100) {
            return redirect()->route('instructor.reporte-reservas-filtro')->with('error', 'Indica un año válido.');
        }

        $mes = $request->filled('mes') ? (int) $request->mes : null;
        if ($mes !== null && ($mes < 1 || $mes > 12)) {
            return redirect()->route('instructor.reporte-reservas-filtro')->with('error', 'Mes no válido.');
        }

        $semanaInicio = $request->filled('semana_inicio') ? (string) $request->semana_inicio : null;
        if ($semanaInicio !== null && $mes === null) {
            return redirect()->route('instructor.reporte-reservas-filtro', ['anio' => $anio])->with('error', 'Para filtrar por semana elija también un mes.');
        }

        [$desde, $hasta] = $this->resolverRangoExporteInstructor($anio, $mes, $semanaInicio);

        $filas = $this->construirFilasPdfInstructorPorRango($idPersona, $desde, $hasta);
        if ($filas->isEmpty()) {
            return redirect()->route('instructor.reporte-reservas-filtro', array_filter([
                'anio' => $anio,
                'mes' => $mes,
            ]))->with('info', 'No hay sesiones registradas en el periodo seleccionado.');
        }

        $labelDesde = Carbon::parse($desde)->format('d/m/Y');
        $labelHasta = Carbon::parse($hasta)->format('d/m/Y');
        $subtitulo = "Periodo: {$labelDesde} al {$labelHasta}";
        $filename = 'mis_reservas_'.$desde.'_'.$hasta.'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.instructor-reservas-por-fecha', [
            'filas' => $filas,
            'subtitulo' => $subtitulo,
        ])->download($filename);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function semanasEnMes(int $anio, int $mes): array
    {
        $monthStart = Carbon::create($anio, $mes, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $cursor = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $out = [];
        $seen = [];

        while ($cursor->lte($monthEnd)) {
            $key = $cursor->format('Y-m-d');
            if (isset($seen[$key])) {
                $cursor->addWeek();

                continue;
            }
            $seen[$key] = true;
            $sun = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            $clipStart = $cursor->copy()->max($monthStart);
            $clipEnd = $sun->copy()->min($monthEnd);
            if ($clipStart->lte($clipEnd)) {
                $out[] = [
                    'value' => $cursor->format('Y-m-d'),
                    'label' => 'Semana '.$clipStart->format('d/m').' – '.$clipEnd->format('d/m/Y'),
                ];
            }
            $cursor->addWeek();
        }

        return $out;
    }

    /**
     * @return array{0: string, 1: string} fechas Y-m-d inclusive
     */
    private function resolverRangoExporteInstructor(int $anio, ?int $mes, ?string $semanaInicioLunes): array
    {
        if ($mes === null || $mes < 1 || $mes > 12) {
            $from = Carbon::create($anio, 1, 1)->format('Y-m-d');
            $to = Carbon::create($anio, 12, 31)->format('Y-m-d');

            return [$from, $to];
        }

        $monthStart = Carbon::create($anio, $mes, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if ($semanaInicioLunes === null || $semanaInicioLunes === '') {
            return [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')];
        }

        $mon = Carbon::parse($semanaInicioLunes)->startOfWeek(Carbon::MONDAY);
        $sun = $mon->copy()->endOfWeek(Carbon::SUNDAY);
        $from = $mon->max($monthStart)->format('Y-m-d');
        $to = $sun->min($monthEnd)->format('Y-m-d');

        return [$from, $to];
    }

    /**
     * Una fila por cada fecha de clase (no liberada) dentro del rango.
     *
     * @return Collection<int, object>
     */
    private function construirFilasPdfInstructorPorRango(int $idPersona, string $desde, string $hasta): Collection
    {
        $reservas = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('programa', 'ficha.id_programa', '=', 'programa.id_programa')
            ->leftJoin('competencia', 'reservas.id_competencia', '=', 'competencia.id_competencia')
            ->leftJoin('resultados', 'reservas.id_resultado', '=', 'resultados.id_resultado')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_estado_reserva', 1)
            ->select(
                'reservas.id_reserva',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'ficha.id_jornada',
                'programa.nombre_programa',
                'competencia.nombre_competencia',
                'resultados.denominacion as resultado_denominacion',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                DB::raw('dia_semana.dia_semana as dia_semana')
            )
            ->orderBy('reservas.fecha_inicio')
            ->get();

        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $ids = $reservas->pluck('id_reserva')->map(fn ($v) => (int) $v)->all();
        $libPorReserva = [];
        if ($ids !== []) {
            $libRows = DB::table('reserva_dias_liberados')->whereIn('id_reserva', $ids)->get(['id_reserva', 'fecha']);
            foreach ($libRows as $row) {
                $f = $row->fecha;
                $ymd = $f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f;
                $libPorReserva[(int) $row->id_reserva][$ymd] = true;
            }
        }

        $filas = collect();
        foreach ($reservas as $r) {
            $obj = (object) [
                'id_reserva' => $r->id_reserva,
                'fecha_inicio' => $r->fecha_inicio,
                'fecha_fin' => $r->fecha_fin,
                'dia_semana' => $r->dia_semana,
            ];
            $fechas = SesionesHelper::fechasClaseReserva($obj, $diasMap);
            $lib = $libPorReserva[(int) $r->id_reserva] ?? [];
            foreach ($fechas as $ymd) {
                if ($ymd < $desde || $ymd > $hasta) {
                    continue;
                }
                if (isset($lib[$ymd])) {
                    continue;
                }
                $filas->push((object) [
                    'fecha_sesion' => $ymd,
                    'num_ambiente' => $r->num_ambiente,
                    'num_ficha' => $r->num_ficha,
                    'id_jornada' => $r->id_jornada,
                    'nombre_programa' => $r->nombre_programa,
                    'nombre_competencia' => $r->nombre_competencia,
                    'resultado_denominacion' => $r->resultado_denominacion,
                    'dia_semana' => $r->dia_semana,
                ]);
            }
        }

        return $filas->sortBy('fecha_sesion')->values();
    }

    /**
     * Liberar un día específico de una reserva: el instructor no usará el ambiente ese día,
     * permitiendo que otro profesor lo reserve. No afecta sesiones ni resultados.
     */
    public function liberarDia(Request $request)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'fecha' => 'required|date',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_persona', $idPersona)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no pertenece a tu usuario.');
        }

        $fecha = \Carbon\Carbon::parse($request->fecha)->startOfDay();
        $fechaInicio = \Carbon\Carbon::parse($reserva->fecha_inicio)->startOfDay();
        $fechaFin = \Carbon\Carbon::parse($reserva->fecha_fin)->startOfDay();

        if ($fecha->lt($fechaInicio) || $fecha->gt($fechaFin)) {
            return redirect()->back()->with('error', 'La fecha debe estar dentro del rango de la reserva (desde la primera hasta la última clase, inclusive).');
        }

        // Verificar que la fecha coincida con el día de la semana de la reserva
        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $reserva->id_dia_semana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $targetDow = $diasMap[$diaTexto] ?? null;
        if ($targetDow !== null && $fecha->dayOfWeek !== $targetDow) {
            return redirect()->back()->with('error', 'La fecha debe ser un '.ucfirst($diaTexto).' (día de la reserva).');
        }

        $existe = ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)
            ->where('fecha', $fecha->format('Y-m-d'))
            ->exists();

        if ($existe) {
            return redirect()->back()->with('info', 'Ese día ya estaba liberado.');
        }

        ReservaDiaLiberado::create([
            'id_reserva' => $reserva->id_reserva,
            'fecha' => $fecha->format('Y-m-d'),
        ]);

        $ruta = SecureRedirect::safeUrl($request->input('redirect'), 'dashboard');

        return redirect($ruta)->with('success', 'Día liberado correctamente. Otro instructor puede reservar ese ambiente para esa fecha.');
    }

    /**
     * Liberar todos los días festivos de Colombia que caen dentro de la reserva.
     * Solo libera festivos que coinciden con el día de la semana de la reserva.
     */
    public function liberarFestivos(Request $request)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_persona', $idPersona)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no pertenece a tu usuario.');
        }

        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $reserva->id_dia_semana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $targetDow = $diasMap[$diaTexto] ?? null;
        if ($targetDow === null) {
            return redirect()->back()->with('error', 'No se pudo determinar el día de la reserva.');
        }

        $festivos = FestivosColombiaHelper::festivosEnRango(
            $reserva->fecha_inicio,
            $reserva->fecha_fin,
            $targetDow
        );

        if (empty($festivos)) {
            return redirect()->back()->with('info', 'No hay días festivos de Colombia dentro del rango de tu reserva para este día de la semana.');
        }

        $yaLiberados = ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)
            ->whereIn('fecha', array_column($festivos, 'fecha'))
            ->pluck('fecha')
            ->map(fn ($f) => $f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f)
            ->toArray();

        $liberados = 0;
        foreach ($festivos as $f) {
            $fechaStr = $f['fecha'];
            if (in_array($fechaStr, $yaLiberados, true)) {
                continue;
            }
            ReservaDiaLiberado::create([
                'id_reserva' => $reserva->id_reserva,
                'fecha' => $fechaStr,
            ]);
            $liberados++;
        }

        $ruta = SecureRedirect::safeUrl($request->input('redirect'), 'dashboard');
        if ($liberados > 0) {
            $msg = $liberados === 1
                ? '1 día festivo liberado correctamente.'
                : "{$liberados} días festivos liberados correctamente.";

            return redirect($ruta)->with('success', $msg);
        }

        return redirect($ruta)->with('info', 'Todos los días festivos de tu reserva ya estaban liberados.');
    }

    /**
     * Revertir: quitar un día liberado (volver a ocupar ese día).
     */
    public function revertirDiaLiberado(Request $request)
    {
        $user = Auth::user();
        if (! $user->isInstructor()) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso.');
        }

        $idPersona = $user->persona->id_persona ?? null;
        if (! $idPersona) {
            return redirect()->route('dashboard')->with('error', 'No se encontró tu perfil.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'fecha' => 'required|date',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_persona', $idPersona)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no pertenece a tu usuario.');
        }

        // Verificar si otra reserva ya ocupa ese ambiente, día, jornada y fecha
        $fecha = $request->fecha;
        $idJornadaFicha = DB::table('ficha')->where('id_ficha', $reserva->id_ficha)->value('id_jornada');
        $otraOcupa = DB::table('reservas')
            ->join('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->where('reservas.id_ambiente', $reserva->id_ambiente)
            ->where('reservas.id_dia_semana', $reserva->id_dia_semana)
            ->where('ficha.id_jornada', $idJornadaFicha)
            ->where('reservas.id_estado_reserva', 1)
            ->where('reservas.id_reserva', '!=', $reserva->id_reserva)
            ->where('reservas.fecha_inicio', '<=', $fecha)
            ->where('reservas.fecha_fin', '>=', $fecha)
            ->whereNotExists(function ($q) use ($fecha) {
                $q->select(DB::raw(1))
                    ->from('reserva_dias_liberados')
                    ->whereColumn('reserva_dias_liberados.id_reserva', 'reservas.id_reserva')
                    ->where('reserva_dias_liberados.fecha', $fecha);
            })
            ->exists();

        if ($otraOcupa) {
            return redirect()->back()->with('error', 'No puedes recuperar ese día porque la coordinación ya asignó otra ficha a ese ambiente en esa fecha y horario.');
        }

        $deleted = ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)
            ->where('fecha', $request->fecha)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Día liberado revertido. Volverás a ocupar ese día.');
        }

        return redirect()->back()->with('info', 'Ese día no estaba liberado.');
    }
}
