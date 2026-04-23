<?php

namespace App\Http\Controllers;

use App\Helpers\AmbienteReservaEstadoHelper;
use App\Helpers\DiaSemanaDesdeFechaHelper;
use App\Helpers\JornadaFichaHelper;
use App\Helpers\ReservaFechasClaseHelper;
use App\Helpers\ReservaPlanFechasHelper;
use App\Helpers\SesionesHelper;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateReservaRequest;
use App\Models\Ambiente;
use App\Models\Ficha;
use App\Models\Persona;
use App\Models\Reserva;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReservasController extends Controller
{
    /**
     * Verifica si hay conflicto de reservas considerando días liberados.
     * Fechas INCLUSIVAS: fecha_inicio y fecha_fin son la primera y última fecha posible de clase.
     * Un conflicto existe si alguna fecha del nuevo rango (que coincida con el día de semana)
     * está ocupada por otra reserva que NO la tiene liberada en `reserva_dias_liberados`.
     * Si un día fue liberado desde la reserva original, el ambiente/jornada vuelve a estar libre
     * (puede programarse otra reserva, incluso con la misma ficha, instructor y ambiente).
     *
     * @return array{conflicto: bool, fechas: string[]} fechas en conflicto (formato d/m/Y)
     */
    private function hayConflictoReserva(int $idAmbiente, int $idDiaSemana, int $idJornada, string $fechaInicio, string $fechaFin, ?int $excluirReservaId = null): array
    {
        $reservasExistentes = DB::table('reservas')
            ->join('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->where('reservas.id_ambiente', $idAmbiente)
            ->where('reservas.id_dia_semana', $idDiaSemana)
            ->where('ficha.id_jornada', $idJornada)
            ->where('reservas.id_estado_reserva', 1)
            ->when($excluirReservaId, fn ($q) => $q->where('id_reserva', '!=', $excluirReservaId))
            ->get(['reservas.id_reserva', 'reservas.id_persona', 'reservas.fecha_inicio', 'reservas.fecha_fin']);

        if ($reservasExistentes->isEmpty()) {
            return ['conflicto' => false, 'fechas' => []];
        }

        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $idDiaSemana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $targetDow = $diasMap[$diaTexto] ?? null;
        if ($targetDow === null) {
            return ['conflicto' => true, 'fechas' => []];
        }

        $inicio = \Carbon\Carbon::parse($fechaInicio);
        $fin = \Carbon\Carbon::parse($fechaFin);
        $fechasEnConflicto = [];

        $fecha = $inicio->copy()->startOfDay();
        $finD = $fin->copy()->startOfDay();
        while ($fecha->lte($finD)) {
            if ($fecha->dayOfWeek !== $targetDow) {
                $fecha->addDay();

                continue;
            }
            $fechaStr = $fecha->format('Y-m-d');

            foreach ($reservasExistentes as $r) {
                $rInicio = \Carbon\Carbon::parse($r->fecha_inicio)->startOfDay();
                $rFin = \Carbon\Carbon::parse($r->fecha_fin)->startOfDay();
                if ($fecha->gte($rInicio) && $fecha->lte($rFin)) {
                    $estaLiberada = DB::table('reserva_dias_liberados')
                        ->where('id_reserva', $r->id_reserva)
                        ->where('fecha', $fechaStr)
                        ->exists();
                    if ($estaLiberada) {
                        continue;
                    }
                    $fechasEnConflicto[$fechaStr] = $fecha->format('d/m/Y');
                }
            }
            $fecha->addDay();
        }

        return [
            'conflicto' => ! empty($fechasEnConflicto),
            'fechas' => array_values($fechasEnConflicto),
        ];
    }

    private function diasMapDowSesiones(): array
    {
        return ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
    }

    /**
     * La ficha ya tiene una sesión activa (cualquier reserva) en esa fecha de calendario.
     */
    private function fichaTieneClaseActivaEnFecha(int $idFicha, string $ymd, ?int $excluirReservaId): bool
    {
        $q = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_estado_reserva', 1);
        if ($excluirReservaId) {
            $q->where('reservas.id_reserva', '!=', $excluirReservaId);
        }
        $reservas = $q->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')->get();
        $map = $this->diasMapDowSesiones();
        foreach ($reservas as $r) {
            foreach (SesionesHelper::fechasClaseReserva($r, $map) as $f) {
                if ($f !== $ymd) {
                    continue;
                }
                $lib = DB::table('reserva_dias_liberados')
                    ->where('id_reserva', $r->id_reserva)
                    ->where('fecha', $ymd)
                    ->exists();
                if (! $lib) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * El instructor ya tiene una sesión activa en esa fecha de calendario.
     */
    private function instructorTieneClaseActivaEnFecha(?int $idPersona, string $ymd, ?int $excluirReservaId): bool
    {
        if (! $idPersona) {
            return false;
        }
        $q = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_estado_reserva', 1);
        if ($excluirReservaId) {
            $q->where('reservas.id_reserva', '!=', $excluirReservaId);
        }
        $reservas = $q->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')->get();
        $map = $this->diasMapDowSesiones();
        foreach ($reservas as $r) {
            foreach (SesionesHelper::fechasClaseReserva($r, $map) as $f) {
                if ($f !== $ymd) {
                    continue;
                }
                $lib = DB::table('reserva_dias_liberados')
                    ->where('id_reserva', $r->id_reserva)
                    ->where('fecha', $ymd)
                    ->exists();
                if (! $lib) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ya existe sesión activa para ficha + competencia + resultado en esa fecha, sin estar liberada en esa fecha.
     */
    private function existeSesionResultadoOcupadaEnFecha(int $idFicha, int $idCompetencia, int $idResultado, string $ymd, ?int $excluirReservaId): bool
    {
        $q = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_competencia', $idCompetencia)
            ->where('reservas.id_resultado', $idResultado)
            ->where('reservas.id_estado_reserva', 1);
        if ($excluirReservaId) {
            $q->where('reservas.id_reserva', '!=', $excluirReservaId);
        }
        $reservas = $q->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')->get();
        $map = $this->diasMapDowSesiones();
        foreach ($reservas as $r) {
            foreach (SesionesHelper::fechasClaseReserva($r, $map) as $f) {
                if ($f !== $ymd) {
                    continue;
                }
                $lib = DB::table('reserva_dias_liberados')
                    ->where('id_reserva', $r->id_reserva)
                    ->where('fecha', $ymd)
                    ->exists();
                if (! $lib) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        // Obtener todos los ambientes para el select
        $ambientes = DB::table('ambientes')
            ->select('id_ambiente', 'num_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente')
            ->get();

        $fichas = DB::table('ficha')
            ->select('id_ficha', 'num_ficha', 'id_programa', 'id_jornada')
            ->orderBy('num_ficha', 'asc')
            ->get();

        $jornadas = config('jornadas');

        $competencias = DB::table('competencia')
            ->select('id_competencia', 'nombre_competencia', 'id_programa')
            ->orderBy('nombre_competencia')
            ->get();

        $instructores = Persona::where('id_rol', config('roles.ids.instructor', 4))
            ->whereHas('user')
            ->orderBy('nombres')
            ->get(['id_persona', 'nombres', 'apellidos']);

        $resultados = DB::table('resultados')
            ->select('id_resultado', 'id_competencia', 'denominacion', 'sesiones')
            ->orderBy('id_competencia')
            ->orderBy('denominacion')
            ->get();

        return view('reservas.create', [
            'ambientes' => $ambientes,
            'fichas' => $fichas,
            'jornadas' => $jornadas,
            'competencias' => $competencias,
            'instructores' => $instructores,
            'resultados' => $resultados,
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(StoreReservaRequest $request)
    {
        $fichaRow = DB::table('ficha')->where('id_ficha', $request->id_ficha)->first();
        if (! $fichaRow || $fichaRow->id_jornada === null) {
            return redirect()->back()->withInput()
                ->with('error', 'La ficha no tiene jornada definida. Actualice la ficha en el módulo de fichas.');
        }
        $idJornada = (int) $fichaRow->id_jornada;
        if (! JornadaFichaHelper::rangoFechasCompatibleConJornadaFicha($request->fecha_inicio, $request->fecha_fin, $idJornada)) {
            return redirect()->back()->withInput()
                ->with('error', 'Las fechas de inicio y fin deben coincidir con la jornada del grupo: mañana, tarde y noche solo en días de lunes a viernes; fin de semana solo en sábado o domingo.');
        }

        $ambiente = DB::table('ambientes')
            ->where('id_ambiente', $request->id_ambiente)
            ->first();

        if ($ambiente) {
            $capacidadMaxima = $ambiente->capacidad_max ?? 35;
            $ficha = DB::table('ficha')
                ->where('id_ficha', $request->id_ficha)
                ->first();

            if ($ficha && $ficha->cant_aprendices > $capacidadMaxima) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "La cantidad de aprendices ({$ficha->cant_aprendices}) excede la capacidad máxima del ambiente ({$capacidadMaxima} aprendices).");
            }
        }

        $diasSeleccionados = $request->input('dias_semana', []);
        if (! is_array($diasSeleccionados)) {
            $diasSeleccionados = [];
        }
        $diasSeleccionados = array_values(array_unique(array_filter(array_map(
            static fn ($d) => strtolower(trim((string) $d)),
            $diasSeleccionados
        ))));

        if ($diasSeleccionados === []) {
            return redirect()->back()->withInput()
                ->with('error', 'Seleccione al menos un día de la semana para las sesiones.');
        }

        $diasPermitidos = ReservaPlanFechasHelper::diasSemanaPermitidosPorJornada($idJornada);
        $permFlip = array_flip($diasPermitidos);
        foreach ($diasSeleccionados as $d) {
            if (! isset($permFlip[$d])) {
                return redirect()->back()->withInput()
                    ->with('error', 'Los días seleccionados deben coincidir con la jornada de la ficha: entre semana solo de lunes a viernes; fin de semana solo sábado y domingo.');
            }
        }

        $fechasCliente = $request->input('fechas_sesion', []);
        if (! is_array($fechasCliente)) {
            $fechasCliente = [];
        }
        $fechasCliente = array_values(array_unique(array_filter(array_map(
            static function ($f) {
                $s = is_string($f) ? trim($f) : '';

                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) ? $s : null;
            },
            $fechasCliente
        ))));
        sort($fechasCliente);

        if ($fechasCliente === []) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe quedar al menos una fecha de sesión en la lista (use el rango y los días de la semana para generarla).');
        }

        $esperadas = ReservaPlanFechasHelper::generarFechasSesion(
            $request->fecha_inicio,
            $request->fecha_fin,
            $diasSeleccionados,
            $idJornada
        );
        $esperadasFlip = array_flip($esperadas);
        $hoy = now()->format('Y-m-d');
        foreach ($fechasCliente as $ymd) {
            if (! isset($esperadasFlip[$ymd])) {
                return redirect()->back()->withInput()
                    ->with('error', 'La lista de fechas no coincide con el rango y los días elegidos. Regenera la lista e intenta de nuevo.');
            }
            if ($ymd < $hoy) {
                return redirect()->back()->withInput()
                    ->with('error', 'No se pueden crear reservas en fechas anteriores a hoy.');
            }
        }

        $idResultado = $request->id_resultado;
        if (! $idResultado) {
            $primerResultado = DB::table('resultados')
                ->where('id_competencia', $request->id_competencia)
                ->orderBy('id_resultado')
                ->first();
            $idResultado = $primerResultado->id_resultado ?? null;
        }
        if (! $idResultado) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe seleccionar un resultado de aprendizaje para la competencia elegida.');
        }

        if (! SesionesHelper::competenciaTieneCupoDisponibleEnAlgunResultado((int) $request->id_ficha, (int) $request->id_competencia)) {
            return redirect()->back()->withInput()
                ->with('error', 'No puede programar más reservas para esta competencia en esta ficha: todos los resultados ya tienen el cupo de sesiones completo en el calendario.');
        }

        $evalSes = SesionesHelper::evaluarSesionesResultadoAgregandoFechas(
            (int) $request->id_ficha,
            (int) $idResultado,
            $fechasCliente,
            null
        );
        if ($evalSes['excede']) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Este resultado de aprendizaje permite '
                    .$evalSes['limite']
                    .' sesión(es). Con las fechas elegidas habría '
                    .$evalSes['total']
                    .' sesión(es) en total (incluye otras reservas activas del mismo resultado). Acorte la lista o elija otro resultado.'
                );
        }

        $batchFicha = [];
        $batchInstructor = [];

        foreach ($fechasCliente as $ymd) {
            if (isset($batchFicha[$ymd])) {
                return redirect()->back()->withInput()->with('error', 'Lista de fechas inválida: fecha duplicada.');
            }

            $diaClave = DiaSemanaDesdeFechaHelper::claveDesdeYmd($ymd);
            $rowDia = $diaClave ? DB::table('dia_semana')->where('dia_semana', $diaClave)->first() : null;
            $idDiaSemana = $rowDia->id_dia_semana ?? null;
            if (! $idDiaSemana) {
                return redirect()->back()->withInput()->with('error', 'No se pudo determinar el día de la semana para la fecha '.$ymd.'.');
            }

            $conflicto = $this->hayConflictoReserva(
                (int) $request->id_ambiente,
                (int) $idDiaSemana,
                (int) $idJornada,
                $ymd,
                $ymd,
                null
            );
            if ($conflicto['conflicto']) {
                $msg = 'Ya existe una reserva activa en este ambiente para el día y horario seleccionado.';
                if (! empty($conflicto['fechas'])) {
                    $msg .= ' Fechas en conflicto: '.implode(', ', $conflicto['fechas']).'.';
                } else {
                    $msg .= ' Por favor, elige otro horario o día.';
                }

                return redirect()->back()->withInput()->with('error', $msg);
            }

            if ($this->fichaTieneClaseActivaEnFecha((int) $request->id_ficha, $ymd, null)) {
                return redirect()->back()->withInput()
                    ->with('error', 'La ficha ya tiene una reserva activa el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'.');
            }

            if ($request->id_persona) {
                if ($this->instructorTieneClaseActivaEnFecha((int) $request->id_persona, $ymd, null) || isset($batchInstructor[$ymd])) {
                    return redirect()->back()->withInput()
                        ->with('error', 'El instructor ya tiene una clase asignada el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'.');
                }
            }

            if ($this->existeSesionResultadoOcupadaEnFecha((int) $request->id_ficha, (int) $request->id_competencia, (int) $idResultado, $ymd, null)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Ya existe una sesión activa con este mismo resultado el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'. Use recuperación solo si hay día liberado registrado para ese resultado.');
            }

            $batchFicha[$ymd] = true;
            if ($request->id_persona) {
                $batchInstructor[$ymd] = true;
            }
        }

        $idsCreados = [];
        $user = Auth::user();

        DB::transaction(function () use ($request, $fechasCliente, $idJornada, $idResultado, &$idsCreados) {
            foreach ($fechasCliente as $ymd) {
                $diaClave = DiaSemanaDesdeFechaHelper::claveDesdeYmd($ymd);
                $rowDia = $diaClave ? DB::table('dia_semana')->where('dia_semana', $diaClave)->first() : null;
                $idDiaSemana = $rowDia->id_dia_semana ?? null;

                $attrs = [
                    'id_ambiente' => $request->id_ambiente,
                    'id_ficha' => $request->id_ficha,
                    'id_persona' => $request->id_persona ?: null,
                    'id_competencia' => $request->id_competencia,
                    'id_resultado' => $idResultado,
                    'id_dia_semana' => $idDiaSemana,
                    'fecha_inicio' => $ymd,
                    'fecha_fin' => $ymd,
                    'id_estado_reserva' => 1,
                ];
                if (Schema::hasColumn('reservas', 'id_jornada')) {
                    $attrs['id_jornada'] = $idJornada;
                }
                $reserva = Reserva::create($attrs);
                $idsCreados[] = $reserva->id_reserva;
            }
        });

        try {
            SecurityAuditLog::create([
                'user_id' => $user?->id_cedula,
                'action' => 'reserva_created',
                'resource_type' => 'reserva',
                'resource_id' => $idsCreados[0] ?? null,
                'description' => 'Creación de '.count($idsCreados).' reserva(s) para ambiente ID '.$request->id_ambiente.' y ficha ID '.$request->id_ficha,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        AmbienteReservaEstadoHelper::actualizarEstadoAmbiente((int) $request->id_ambiente);

        $n = count($idsCreados);
        $msg = $n === 1
            ? 'Reserva creada correctamente.'
            : "Se crearon {$n} reservas correctamente (una por cada fecha de sesión).";

        return redirect()->route('ambientes.index')->with('success', $msg);
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit($id)
    {
        $reserva = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('competencia', 'reservas.id_competencia', '=', 'competencia.id_competencia')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_reserva', $id)
            ->select(
                'reservas.id_reserva',
                'reservas.id_ambiente',
                'reservas.id_ficha',
                'reservas.id_persona',
                'reservas.id_competencia',
                'reservas.id_resultado',
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'reservas.id_estado_reserva',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado'
            )
            ->first();

        if (! $reserva) {
            return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
        }

        // Obtener todos los ambientes para el select
        $ambientes = DB::table('ambientes')
            ->select('id_ambiente', 'num_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente')
            ->get();

        $fichas = DB::table('ficha')
            ->select('id_ficha', 'num_ficha', 'id_programa', 'id_jornada')
            ->orderBy('num_ficha', 'asc')
            ->get();

        $estados = DB::table('estado_reserva')
            ->select('id_estado_reserva', 'nombre_estado')
            ->orderBy('id_estado_reserva')
            ->get();

        $jornadas = config('jornadas');

        $competencias = DB::table('competencia')
            ->select('id_competencia', 'nombre_competencia', 'id_programa')
            ->orderBy('nombre_competencia')
            ->get();
        $instructores = Persona::where('id_rol', config('roles.ids.instructor', 4))
            ->whereHas('user')
            ->orderBy('nombres')
            ->get(['id_persona', 'nombres', 'apellidos']);
        $mapaIdJornada = [1 => 'manana', 2 => 'tarde', 3 => 'noche', 4 => 'fin_semana'];
        $jornadaSeleccionada = $mapaIdJornada[$reserva->id_jornada ?? 0] ?? 'manana';

        $resultados = DB::table('resultados')
            ->select('id_resultado', 'id_competencia', 'denominacion', 'sesiones')
            ->orderBy('id_competencia')
            ->orderBy('denominacion')
            ->get();

        return view('reservas.edit', [
            'reserva' => $reserva,
            'ambientes' => $ambientes,
            'fichas' => $fichas,
            'estados' => $estados,
            'instructores' => $instructores,
            'jornadas' => $jornadas,
            'jornadaSeleccionada' => $jornadaSeleccionada,
            'competencias' => $competencias,
            'resultados' => $resultados,
        ]);
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(UpdateReservaRequest $request, $id)
    {
        // La validación se realiza automáticamente por el Form Request

        // Validar capacidad máxima del ambiente
        $ambiente = DB::table('ambientes')
            ->where('id_ambiente', $request->id_ambiente)
            ->first();

        if ($ambiente) {
            // Obtener capacidad máxima del ambiente (por defecto 35 si no está definida)
            $capacidadMaxima = $ambiente->capacidad_max ?? 35;

            // Obtener cantidad de aprendices de la ficha
            $ficha = DB::table('ficha')
                ->where('id_ficha', $request->id_ficha)
                ->first();

            if ($ficha && $ficha->cant_aprendices > $capacidadMaxima) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "La cantidad de aprendices ({$ficha->cant_aprendices}) excede la capacidad máxima del ambiente ({$capacidadMaxima} aprendices).");
            }
        }

        $fichaUpdate = DB::table('ficha')->where('id_ficha', $request->id_ficha)->first();
        if (! $fichaUpdate || $fichaUpdate->id_jornada === null) {
            return redirect()->back()->withInput()
                ->with('error', 'La ficha no tiene jornada definida.');
        }
        $idJornadaUpdate = (int) $fichaUpdate->id_jornada;
        if (! JornadaFichaHelper::rangoFechasCompatibleConJornadaFicha($request->fecha_inicio, $request->fecha_fin, $idJornadaUpdate)) {
            return redirect()->back()->withInput()
                ->with('error', 'Las fechas de inicio y fin deben coincidir con la jornada del grupo: entre semana solo lunes a viernes; fin de semana solo sábado o domingo.');
        }

        $diaClave = DiaSemanaDesdeFechaHelper::claveDesdeYmd($request->fecha_inicio);
        $rowDia = $diaClave ? DB::table('dia_semana')->where('dia_semana', $diaClave)->first() : null;
        $idDiaSemana = $rowDia->id_dia_semana ?? null;
        if (! $idDiaSemana) {
            return redirect()->back()->withInput()->with('error', 'No se pudo determinar el día de la semana a partir de la fecha de inicio.');
        }

        // Validar conflicto: mismo ambiente + mismo día + misma jornada (considera días liberados)
        // Fechas inclusivas: primera y última fecha de clase
        $conflicto = $this->hayConflictoReserva(
            (int) $request->id_ambiente,
            (int) $idDiaSemana,
            (int) $idJornadaUpdate,
            $request->fecha_inicio,
            $request->fecha_fin,
            (int) $id
        );
        if ($conflicto['conflicto']) {
            $msg = 'Ya existe una reserva activa en este ambiente para el día y horario seleccionado.';
            if (! empty($conflicto['fechas'])) {
                $msg .= ' Fechas en conflicto: '.implode(', ', $conflicto['fechas']).'.';
            } else {
                $msg .= ' Por favor, elige otro horario o día.';
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $reservaActual = Reserva::find($id);
        if (! $reservaActual) {
            return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
        }

        $idResultado = $request->id_resultado ?? $reservaActual->id_resultado;
        if ($idResultado) {
            $resultadoValido = DB::table('resultados')
                ->where('id_resultado', $idResultado)
                ->where('id_competencia', $request->id_competencia)
                ->exists();
            if (! $resultadoValido) {
                $idResultado = null;
            }
        }
        if (! $idResultado) {
            $primerResultado = DB::table('resultados')
                ->where('id_competencia', $request->id_competencia)
                ->orderBy('id_resultado')
                ->first();
            $idResultado = $primerResultado->id_resultado ?? null;
        }

        $diaNombreSes = DB::table('dia_semana')->where('id_dia_semana', $idDiaSemana)->value('dia_semana');
        $rowSes = (object) [
            'id_reserva' => (int) $id,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'dia_semana' => $diaNombreSes ? (string) $diaNombreSes : '',
        ];
        $fechasEstaReserva = SesionesHelper::fechasClaseReserva($rowSes, $this->diasMapDowSesiones());

        foreach ($fechasEstaReserva as $ymd) {
            if ($request->id_persona && $this->instructorTieneClaseActivaEnFecha((int) $request->id_persona, $ymd, (int) $id)) {
                return redirect()->back()->withInput()
                    ->with('error', 'El instructor ya tiene otra clase asignada el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'.');
            }
            if ($this->fichaTieneClaseActivaEnFecha((int) $request->id_ficha, $ymd, (int) $id)) {
                return redirect()->back()->withInput()
                    ->with('error', 'La ficha ya tiene otra reserva activa el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'.');
            }
            if ($this->existeSesionResultadoOcupadaEnFecha((int) $request->id_ficha, (int) $request->id_competencia, (int) $idResultado, $ymd, (int) $id)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Ya existe otra sesión activa con este mismo resultado el '.\Carbon\Carbon::parse($ymd)->format('d/m/Y').'.');
            }
        }

        $evalSes = SesionesHelper::evaluarSesionesResultadoAgregandoFechas(
            (int) $request->id_ficha,
            (int) $idResultado,
            $fechasEstaReserva,
            (int) $id
        );
        if ($evalSes['excede']) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Este resultado de aprendizaje permite '
                    .$evalSes['limite']
                    .' sesión(es). Con las fechas de esta reserva el total sería '
                    .$evalSes['total']
                    .' sesión(es) (incluye otras reservas activas del mismo resultado). '
                    .'Ajuste el resultado o las fechas para no superar '
                    .$evalSes['limite']
                    .' sesión(es).'
                );
        }

        $reserva = $reservaActual;

        $idAmbienteAnterior = $reserva->id_ambiente;

        $attrsUpdate = [
            'id_ambiente' => $request->id_ambiente,
            'id_ficha' => $request->id_ficha,
            'id_persona' => $request->id_persona ?: null,
            'id_competencia' => $request->id_competencia,
            'id_resultado' => $idResultado,
            'id_dia_semana' => $idDiaSemana,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'id_estado_reserva' => $request->id_estado_reserva,
        ];
        if (Schema::hasColumn('reservas', 'id_jornada')) {
            $attrsUpdate['id_jornada'] = $idJornadaUpdate;
        }
        $reserva->update($attrsUpdate);

        // Registrar auditoría de actualización de reserva
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id' => $user?->id_cedula,
                'action' => 'reserva_updated',
                'resource_type' => 'reserva',
                'resource_id' => $reserva->id_reserva ?? $id,
                'description' => 'Actualización de reserva ID '.$id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        AmbienteReservaEstadoHelper::actualizarEstadoAmbiente((int) $request->id_ambiente);

        if ($idAmbienteAnterior != $request->id_ambiente) {
            AmbienteReservaEstadoHelper::actualizarEstadoAmbiente((int) $idAmbienteAnterior);
        }

        return redirect()->route('ambientes.index')->with('success', 'Reserva actualizada correctamente.');
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $reserva = Reserva::find($id);

            if (! $reserva) {
                return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
            }

            if ((int) $reserva->id_estado_reserva === 1) {
                $diaSemanaTxt = DB::table('dia_semana')
                    ->where('id_dia_semana', $reserva->id_dia_semana)
                    ->value('dia_semana');
                if (ReservaFechasClaseHelper::tieneAlgunaClaseFutura(
                    $reserva->fecha_inicio,
                    $reserva->fecha_fin,
                    $diaSemanaTxt ? (string) $diaSemanaTxt : null
                )) {
                    return redirect()->route('ambientes.index')->with(
                        'error',
                        'No se puede eliminar esta reserva: aún tiene fechas de clase por cumplir. Cuando termine el periodo o el sistema la marque como finalizada, podrá eliminarla.'
                    );
                }
            }

            $idAmbiente = $reserva->id_ambiente;
            $idReserva = $reserva->id_reserva;

            DB::transaction(function () use ($reserva) {
                if (Schema::hasTable('reserva_dias_liberados')) {
                    DB::table('reserva_dias_liberados')->where('id_reserva', $reserva->id_reserva)->delete();
                }
                $reserva->delete();
            });

            AmbienteReservaEstadoHelper::actualizarEstadoAmbiente((int) $idAmbiente);

            // Registrar auditoría de eliminación de reserva
            try {
                $user = Auth::user();
                SecurityAuditLog::create([
                    'user_id' => $user?->id_cedula,
                    'action' => 'reserva_deleted',
                    'resource_type' => 'reserva',
                    'resource_id' => $idReserva ?? $id,
                    'description' => 'Eliminación de reserva ID '.$id,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status' => 'success',
                    'metadata' => null,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // No romper el flujo si falla el log
            }

            return redirect()->route('ambientes.index')->with('success', 'Reserva eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar reserva: '.$e->getMessage());

            return redirect()->route('ambientes.index')->with('error', 'Error al eliminar la reserva. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Elimina varias reservas creadas como lote (una fila por fecha), misma acción de negocio que destroy.
     */
    public function destroyLote(Request $request)
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_numeric($v) ? (int) $v : null,
            $ids
        ))));
        if ($ids === []) {
            return redirect()->route('ambientes.index')->with('error', 'No se indicaron reservas a eliminar.');
        }

        $reservas = Reserva::whereIn('id_reserva', $ids)->get();
        if ($reservas->count() !== count($ids)) {
            return redirect()->route('ambientes.index')->with('error', 'Algunas reservas ya no existen o la petición no es válida.');
        }

        foreach ($reservas as $reserva) {
            if ((int) $reserva->id_estado_reserva === 1) {
                $diaSemanaTxt = DB::table('dia_semana')
                    ->where('id_dia_semana', $reserva->id_dia_semana)
                    ->value('dia_semana');
                if (ReservaFechasClaseHelper::tieneAlgunaClaseFutura(
                    $reserva->fecha_inicio,
                    $reserva->fecha_fin,
                    $diaSemanaTxt ? (string) $diaSemanaTxt : null
                )) {
                    return redirect()->route('ambientes.index')->with(
                        'error',
                        'No se puede eliminar: al menos una fecha de esta asignación aún tiene clases por cumplir.'
                    );
                }
            }
        }

        $idsAmbientes = [];
        try {
            DB::transaction(function () use ($reservas) {
                foreach ($reservas as $reserva) {
                    if (Schema::hasTable('reserva_dias_liberados')) {
                        DB::table('reserva_dias_liberados')->where('id_reserva', $reserva->id_reserva)->delete();
                    }
                    $reserva->delete();
                }
            });

            foreach ($reservas as $reserva) {
                $idsAmbientes[(int) $reserva->id_ambiente] = true;
            }
            foreach (array_keys($idsAmbientes) as $idAmb) {
                AmbienteReservaEstadoHelper::actualizarEstadoAmbiente($idAmb);
            }

            try {
                $user = Auth::user();
                SecurityAuditLog::create([
                    'user_id' => $user?->id_cedula,
                    'action' => 'reserva_deleted_lote',
                    'resource_type' => 'reserva',
                    'resource_id' => $ids[0] ?? null,
                    'description' => 'Eliminación en lote de reservas IDs: '.implode(', ', $ids),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status' => 'success',
                    'metadata' => null,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // No romper el flujo si falla el log
            }

            $n = count($ids);

            return redirect()->route('ambientes.index')->with(
                'success',
                $n === 1
                    ? 'Reserva eliminada correctamente.'
                    : "Se eliminaron {$n} reservas de la asignación correctamente."
            );
        } catch (\Exception $e) {
            Log::error('Error al eliminar lote de reservas: '.$e->getMessage());

            return redirect()->route('ambientes.index')->with('error', 'Error al eliminar las reservas. Por favor, inténtalo de nuevo.');
        }
    }
}
