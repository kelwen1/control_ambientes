<?php

namespace App\Http\Controllers;

use App\Helpers\ReservaFechasClaseHelper;
use App\Models\Ambiente;
use App\Models\Persona;
use App\Models\ReservaDiaLiberado;
use App\Support\ExcelHtmlExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AmbientesController extends Controller
{
    /** Orden de visualización de días (lunes primero … domingo último). */
    private const ORDEN_DIA_CLAVE = [
        'lunes' => 1,
        'martes' => 2,
        'miercoles' => 3,
        'jueves' => 4,
        'viernes' => 5,
        'sabado' => 6,
        'domingo' => 7,
    ];

    private const MAPA_JORNADAS = ['manana' => 1, 'tarde' => 2, 'noche' => 3, 'fin_semana' => 4];

    /**
     * Disponibilidad de instructor: calendario que muestra en ROJO los días ocupados
     * y en VERDE los días libres por jornada y día de la semana.
     * GET /ambientes/disponibilidad?id_persona=&dia_semana=&jornada=&mes=&anio=
     */
    public function disponibilidad(Request $request)
    {
        $idPersona = $request->get('id_persona');
        $diaSemana = $request->get('dia_semana');
        $jornada = $request->get('jornada');
        $mes = (int) ($request->get('mes') ?: now()->month);
        $anio = (int) ($request->get('anio') ?: now()->year);

        $instructores = Persona::where('id_rol', config('roles.ids.instructor', 4))
            ->whereHas('user')
            ->orderBy('nombres')
            ->get(['id_persona', 'nombres', 'apellidos']);

        $diasSemana = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        $jornadas = config('jornadas');
        $fechasOcupadas = collect();

        if ($idPersona && $diaSemana && $jornada && isset(self::MAPA_JORNADAS[$jornada])) {
            $esFinDeSemana = in_array($diaSemana, ['sabado', 'domingo'], true);
            if ($esFinDeSemana) {
                $jornada = 'fin_semana';
            }
            $idJornada = self::MAPA_JORNADAS[$jornada] ?? 0;

            $rowDia = DB::table('dia_semana')->where('dia_semana', $diaSemana)->first();
            $idDiaSemana = $rowDia->id_dia_semana ?? null;

            if ($idDiaSemana) {
                $fechasOcupadas = $this->obtenerFechasOcupadasInstructor((int) $idPersona, $idDiaSemana, $idJornada);
            }
        }

        $mesInicio = Carbon::createFromDate($anio, $mes, 1);
        $mesFin = $mesInicio->copy()->endOfMonth();

        return view('reservas.disponibilidad', [
            'instructores' => $instructores,
            'diasSemana' => $diasSemana,
            'jornadas' => $jornadas,
            'id_persona' => $idPersona,
            'dia_semana' => $diaSemana,
            'jornada' => $jornada,
            'fechasOcupadas' => $fechasOcupadas,
            'mes' => $mes,
            'anio' => $anio,
            'mesInicio' => $mesInicio,
            'mesFin' => $mesFin,
        ]);
    }

    /**
     * Disponibilidad de ambiente: calendario que muestra en ROJO los días ocupados
     * y en VERDE los días libres por jornada y día de la semana.
     * GET /espacios/disponibilidad-ambiente?id_ambiente=&dia_semana=&jornada=&mes=&anio=
     */
    public function disponibilidadAmbiente(Request $request)
    {
        $idAmbiente = $request->get('id_ambiente');
        $diaSemana = $request->get('dia_semana');
        $jornada = $request->get('jornada');
        $mes = (int) ($request->get('mes') ?: now()->month);
        $anio = (int) ($request->get('anio') ?: now()->year);

        $ambientes = Ambiente::orderBy('num_ambiente')->get(['id_ambiente', 'num_ambiente']);

        $diasSemana = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        $jornadas = config('jornadas');
        $fechasOcupadas = collect();

        if ($idAmbiente && $diaSemana && $jornada && isset(self::MAPA_JORNADAS[$jornada])) {
            $esFinDeSemana = in_array($diaSemana, ['sabado', 'domingo'], true);
            if ($esFinDeSemana) {
                $jornada = 'fin_semana';
            }
            $idJornada = self::MAPA_JORNADAS[$jornada] ?? 0;

            $rowDia = DB::table('dia_semana')->where('dia_semana', $diaSemana)->first();
            $idDiaSemana = $rowDia->id_dia_semana ?? null;

            if ($idDiaSemana) {
                $fechasOcupadas = $this->obtenerFechasOcupadasAmbiente((int) $idAmbiente, $idDiaSemana, $idJornada);
            }
        }

        $mesInicio = Carbon::createFromDate($anio, $mes, 1);
        $mesFin = $mesInicio->copy()->endOfMonth();

        return view('reservas.disponibilidad-ambiente', [
            'ambientes' => $ambientes,
            'diasSemana' => $diasSemana,
            'jornadas' => $jornadas,
            'id_ambiente' => $idAmbiente,
            'dia_semana' => $diaSemana,
            'jornada' => $jornada,
            'fechasOcupadas' => $fechasOcupadas,
            'mes' => $mes,
            'anio' => $anio,
            'mesInicio' => $mesInicio,
            'mesFin' => $mesFin,
        ]);
    }

    /**
     * Obtiene las fechas en las que el ambiente tiene reserva ocupada (no liberada)
     * para ese día de la semana y jornada.
     */
    private function obtenerFechasOcupadasAmbiente(int $idAmbiente, int $idDiaSemana, int $idJornada): \Illuminate\Support\Collection
    {
        $reservas = DB::table('reservas')
            ->join('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->where('reservas.id_ambiente', $idAmbiente)
            ->where('reservas.id_dia_semana', $idDiaSemana)
            ->where('ficha.id_jornada', $idJornada)
            ->where('reservas.id_estado_reserva', 1)
            ->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin')
            ->get();

        $dowMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 7];
        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $idDiaSemana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $dowTarget = $dowMap[$diaTexto] ?? 1;

        $fechas = collect();
        foreach ($reservas as $r) {
            $inicio = Carbon::parse($r->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($r->fecha_fin)->startOfDay();
            $liberados = ReservaDiaLiberado::where('id_reserva', $r->id_reserva)
                ->get()
                ->mapWithKeys(function ($row) {
                    $f = $row->fecha;

                    return [$f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f => true];
                })
                ->toArray();

            $fecha = $inicio->copy();
            while ($fecha->lte($fin)) {
                if ($fecha->dayOfWeekIso === $dowTarget) {
                    $fechaStr = $fecha->format('Y-m-d');
                    if (! isset($liberados[$fechaStr])) {
                        $fechas->push($fechaStr);
                    }
                }
                $fecha->addDay();
            }
        }

        return $fechas->unique()->values();
    }

    /**
     * Obtiene las fechas en las que el instructor tiene reserva ocupada (no liberada)
     * para ese día de la semana y jornada.
     */
    private function obtenerFechasOcupadasInstructor(int $idPersona, int $idDiaSemana, int $idJornada): \Illuminate\Support\Collection
    {
        $reservas = DB::table('reservas')
            ->join('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->where('reservas.id_persona', $idPersona)
            ->where('reservas.id_dia_semana', $idDiaSemana)
            ->where('ficha.id_jornada', $idJornada)
            ->where('reservas.id_estado_reserva', 1)
            ->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin')
            ->get();

        $dowMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 7];
        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $idDiaSemana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $dowTarget = $dowMap[$diaTexto] ?? 1;

        $fechas = collect();
        foreach ($reservas as $r) {
            $inicio = Carbon::parse($r->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($r->fecha_fin)->startOfDay();
            $liberados = ReservaDiaLiberado::where('id_reserva', $r->id_reserva)
                ->get()
                ->mapWithKeys(function ($row) {
                    $f = $row->fecha;

                    return [$f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f => true];
                })
                ->toArray();

            $fecha = $inicio->copy();
            while ($fecha->lte($fin)) {
                if ($fecha->dayOfWeekIso === $dowTarget) {
                    $fechaStr = $fecha->format('Y-m-d');
                    if (! isset($liberados[$fechaStr])) {
                        $fechas->push($fechaStr);
                    }
                }
                $fecha->addDay();
            }
        }

        return $fechas->unique()->values();
    }

    /**
     * Agrupa filas de reserva que en BD son una fecha por registro pero se crearon en el mismo lote
     * (mismo contexto + mismo minuto de created_at). Los rangos legacy (fecha_inicio ≠ fecha_fin) no se fusionan.
     */
    private function claveAgrupacionVisualReserva(object $row): string
    {
        $ini = (string) ($row->fecha_inicio ?? '');
        $fin = (string) ($row->fecha_fin ?? '');
        if ($ini !== $fin) {
            return 'legacy-'.(int) $row->id_reserva;
        }

        if (empty($row->created_at)) {
            return 'sd-sin-marca-'.(int) $row->id_reserva;
        }

        $minuto = Carbon::parse($row->created_at)->format('Y-m-d H:i');

        return implode('|', [
            'sd',
            (int) ($row->id_ambiente ?? 0),
            (int) ($row->id_ficha ?? 0),
            (int) ($row->id_persona ?? 0),
            (int) ($row->id_competencia ?? 0),
            (int) ($row->id_resultado ?? 0),
            (int) ($row->id_estado_reserva ?? 0),
            (int) ($row->id_jornada ?? 0),
            $minuto,
        ]);
    }

    /**
     * @param  array<int, object>  $items  filas DB del mismo grupo visual
     */
    private function fusionarGrupoReservasVisual(array $items): object
    {
        usort($items, static function ($a, $b) {
            $fa = strcmp((string) ($a->fecha_inicio ?? ''), (string) ($b->fecha_inicio ?? ''));
            if ($fa !== 0) {
                return $fa;
            }

            return (int) $a->id_reserva <=> (int) $b->id_reserva;
        });

        $diasLabels = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
        $base = $items[0];
        $ini = (string) ($base->fecha_inicio ?? '');
        $fin = (string) ($base->fecha_fin ?? '');

        if (count($items) === 1 || $ini !== $fin) {
            $fc = ReservaFechasClaseHelper::fechasClaseEnRangoInclusivo(
                $base->fecha_inicio ?? null,
                $base->fecha_fin ?? null,
                $base->dia_semana ?? null
            );
            $base->fecha_primera_clase = $fc['labelPrimera'];
            $base->fecha_ultima_clase = $fc['labelUltima'];
            $base->fechas_clase_modal = array_map(
                static fn (string $ymd) => Carbon::parse($ymd)->format('d/m/Y'),
                $fc['fechas']
            );
            $base->etiqueta_dias_listado = $diasLabels[$base->dia_semana ?? ''] ?? ucfirst((string) ($base->dia_semana ?? 'N/A'));
            $base->modal_etiqueta_dia = $base->etiqueta_dias_listado;
            $base->reserva_ids_grupo = [(int) $base->id_reserva];
            $base->puede_eliminar_reserva = true;
            if ((int) ($base->id_estado_reserva ?? 0) === 1) {
                $base->puede_eliminar_reserva = ! ReservaFechasClaseHelper::tieneAlgunaClaseFutura(
                    $base->fecha_inicio ?? null,
                    $base->fecha_fin ?? null,
                    $base->dia_semana ?? null
                );
            }

            return $base;
        }

        $ymds = [];
        $clavesDia = [];
        $idOriginal = null;
        $puedeEliminar = true;
        $idsGrupo = [];

        foreach ($items as $row) {
            $idsGrupo[] = (int) $row->id_reserva;
            $d = (string) ($row->fecha_inicio ?? '');
            if ($d !== '') {
                $ymds[$d] = true;
            }
            $c = strtolower(trim((string) ($row->dia_semana ?? '')));
            if ($c !== '') {
                $clavesDia[$c] = true;
            }
            if ($idOriginal === null && ! empty($row->id_reserva_original)) {
                $idOriginal = (int) $row->id_reserva_original;
            }
            if ((int) ($row->id_estado_reserva ?? 0) === 1) {
                $ok = ! ReservaFechasClaseHelper::tieneAlgunaClaseFutura(
                    $row->fecha_inicio ?? null,
                    $row->fecha_fin ?? null,
                    $row->dia_semana ?? null
                );
                if (! $ok) {
                    $puedeEliminar = false;
                }
            }
        }

        $listaYmd = array_keys($ymds);
        sort($listaYmd);
        $primera = $listaYmd[0] ?? null;
        $ultima = count($listaYmd) > 0 ? $listaYmd[count($listaYmd) - 1] : null;

        $ordenados = array_keys($clavesDia);
        usort($ordenados, function ($a, $b) {
            $oa = self::ORDEN_DIA_CLAVE[$a] ?? 99;
            $ob = self::ORDEN_DIA_CLAVE[$b] ?? 99;

            return $oa <=> $ob;
        });
        $etiquetasDias = array_map(function ($c) use ($diasLabels) {
            return $diasLabels[$c] ?? ucfirst($c);
        }, $ordenados);
        $etiquetaLista = count($etiquetasDias) > 1
            ? implode(', ', $etiquetasDias)
            : ($etiquetasDias[0] ?? 'N/A');
        $modalDia = count($etiquetasDias) > 1
            ? 'Varios días ('.implode(', ', $etiquetasDias).')'
            : ($etiquetasDias[0] ?? '—');

        $representante = $items[0];
        $representante->fecha_inicio = $primera;
        $representante->fecha_fin = $ultima;
        $representante->fecha_primera_clase = $primera ? Carbon::parse($primera)->format('d/m/Y') : null;
        $representante->fecha_ultima_clase = $ultima ? Carbon::parse($ultima)->format('d/m/Y') : null;
        $representante->fechas_clase_modal = array_map(
            static fn (string $ymd) => Carbon::parse($ymd)->format('d/m/Y'),
            $listaYmd
        );
        $representante->etiqueta_dias_listado = $etiquetaLista;
        $representante->modal_etiqueta_dia = $modalDia;
        $representante->dia_semana = $ordenados[0] ?? $representante->dia_semana;
        $representante->id_reserva = (int) $representante->id_reserva;
        $representante->id_reserva_original = $idOriginal;
        $representante->reserva_ids_grupo = $idsGrupo;
        $representante->puede_eliminar_reserva = $puedeEliminar;

        return $representante;
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
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->select(
                'reservas.id_reserva',
                'reservas.id_ambiente',
                'reservas.id_ficha',
                'reservas.id_persona',
                'reservas.id_competencia',
                'reservas.id_resultado',
                'reservas.created_at',
                'ficha.id_jornada',
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'reservas.id_estado_reserva',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado',
                DB::raw('(SELECT r2.id_reserva FROM reserva_dias_liberados rdl
                    INNER JOIN reservas r2 ON r2.id_reserva = rdl.id_reserva AND r2.id_reserva != reservas.id_reserva
                    INNER JOIN ficha f2 ON f2.id_ficha = r2.id_ficha
                    WHERE r2.id_ambiente = reservas.id_ambiente
                      AND r2.id_dia_semana = reservas.id_dia_semana
                      AND f2.id_jornada = ficha.id_jornada
                      AND r2.id_estado_reserva = 1
                      AND rdl.fecha >= reservas.fecha_inicio
                      AND rdl.fecha <= reservas.fecha_fin
                    LIMIT 1) as id_reserva_original')
            )
            ->orderBy('reservas.id_reserva', 'desc');

        // Búsqueda solo por número de ambiente
        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('ambientes.num_ambiente', 'like', '%'.$search.'%');
        }

        $rawRows = $query->get();
        $buckets = [];
        foreach ($rawRows as $row) {
            $k = $this->claveAgrupacionVisualReserva($row);
            $buckets[$k] ??= [];
            $buckets[$k][] = $row;
        }

        $agrupadas = collect($buckets)
            ->map(fn (array $items) => $this->fusionarGrupoReservasVisual($items))
            ->sortByDesc(function (object $g) {
                return max($g->reserva_ids_grupo ?? [(int) $g->id_reserva]);
            })
            ->values();

        $perPage = 10;
        $page = max(1, (int) $request->get('page', 1));
        $total = $agrupadas->count();
        $slice = $agrupadas->forPage($page, $perPage)->values();

        $reservas = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('reservas.index', [
            'reservas' => $reservas,
            'search' => $request->search ?? '',
        ]);
    }

    /**
     * Devuelve JSON con los datos de una reserva (para el modal de reserva original).
     */
    public function reservaOriginal($id)
    {
        $mapaJornadas = [1 => 'Mañana', 2 => 'Tarde', 3 => 'Noche', 4 => 'Fin de semana'];
        $reserva = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('persona', 'reservas.id_persona', '=', 'persona.id_persona')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_reserva', $id)
            ->select(
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                DB::raw("CONCAT(COALESCE(persona.nombres,''), ' ', COALESCE(persona.apellidos,'')) as instructor"),
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'estado_reserva.nombre_estado'
            )
            ->first();

        if (! $reserva) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $diasLabels = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
        $dia = $diasLabels[$reserva->dia_semana ?? ''] ?? ucfirst($reserva->dia_semana ?? 'N/A');
        $jornada = $mapaJornadas[$reserva->id_jornada ?? 0] ?? 'N/A';
        $fc = ReservaFechasClaseHelper::fechasClaseEnRangoInclusivo(
            $reserva->fecha_inicio ?? null,
            $reserva->fecha_fin ?? null,
            $reserva->dia_semana ?? null
        );
        $fechas = ($fc['labelPrimera'] && $fc['labelUltima'])
            ? $fc['labelPrimera'].' - '.$fc['labelUltima']
            : 'N/A';

        return response()->json([
            'ambiente' => $reserva->num_ambiente ?? 'N/A',
            'ficha' => $reserva->num_ficha ?? 'N/A',
            'instructor' => trim($reserva->instructor ?? '') ?: 'Sin asignar',
            'dia' => $dia,
            'jornada' => $jornada,
            'fechas' => $fechas,
            'estado' => $reserva->nombre_estado ?? 'N/A',
        ]);
    }

    /**
     * Export reservas a PDF
     */
    public function export(Request $request)
    {
        if (auth()->user()->isInstructor()) {
            abort(403);
        }

        $reservas = $this->reservasCollectionParaExport($request);
        $filename = 'ambientes_reservas_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.ambientes', compact('reservas'))
            ->download($filename);
    }

    /**
     * Export reservas a Excel (HTML .xls, mismo criterio de filtro que el PDF)
     */
    public function exportExcel(Request $request)
    {
        if (auth()->user()->isInstructor()) {
            abort(403);
        }

        $reservas = $this->reservasCollectionParaExport($request);

        return ExcelHtmlExport::download('exports.ambientes_excel', compact('reservas'), 'ambientes_reservas');
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function reservasCollectionParaExport(Request $request)
    {
        $query = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->leftJoin('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->select(
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado',
                DB::raw('dia_semana.dia_semana as dia_semana'),
                'ficha.id_jornada',
                'reservas.fecha_inicio',
                'reservas.fecha_fin'
            )
            ->orderBy('reservas.id_reserva', 'desc');

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('ambientes.num_ambiente', 'like', '%'.$search.'%');
        }

        return $query->get();
    }
}
