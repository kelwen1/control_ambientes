<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SesionesHelper
{
    /** @return array<string, int> */
    private static function diasMapDow(): array
    {
        return ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
    }

    /**
     * Fechas (Y-m-d) de clase según el día de la reserva, entre fecha_inicio y fecha_fin (inclusivo).
     *
     * @param  object{id_reserva:int|string,fecha_inicio:mixed,fecha_fin:mixed,dia_semana?:string}  $r
     * @return list<string>
     */
    public static function fechasClaseReserva(object $r, array $diasMap): array
    {
        $diaTexto = $r->dia_semana ?? '';
        $targetDow = $diasMap[strtolower(trim((string) $diaTexto))] ?? null;
        if ($targetDow === null) {
            return [];
        }
        $fechas = [];
        $inicio = \Carbon\Carbon::parse($r->fecha_inicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($r->fecha_fin)->startOfDay();
        $f = $inicio->copy();
        while ($f->lte($fin)) {
            if ($f->dayOfWeek === $targetDow) {
                $fechas[] = $f->format('Y-m-d');
            }
            $f->addDay();
        }

        return $fechas;
    }

    /**
     * Calcula sesiones totales, consumidas y restantes para una ficha+competencia.
     * — Impartidas: fechas de clase ya pasadas y no liberadas.
     * — Suspendidas: fechas marcadas en reserva_dias_liberados (cuentan para el cupo; no “liberan” sesión curricular).
     * — Restantes (pendientes de impartir): fechas de clase futuras (posterior a hoy) y no liberadas; baja al pasar cada fecha.
     * — Cupo libre en calendario: total − fechas ya ocupadas en el calendario (pasadas, futuras o liberadas); espacio para agendar nuevas fechas.
     * Todo en PHP por reserva/ficha (sin tocar resultados.sesiones en BD; compartido entre fichas).
     *
     * @return array{total: int, consumidas: int, suspendidas: int, restantes: int, cupo_libre_calendario: int}
     */
    public static function calcularSesionesFichaCompetencia(int $idFicha, int $idCompetencia): array
    {
        $totalSesiones = (int) DB::table('resultados')
            ->where('id_competencia', $idCompetencia)
            ->sum('sesiones');

        $reservas = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_competencia', $idCompetencia)
            ->where('reservas.id_estado_reserva', 1)
            ->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')
            ->get();

        $diasMap = self::diasMapDow();
        $hoy = now()->startOfDay();
        $hoyStr = $hoy->format('Y-m-d');

        $fechasComprometidas = [];
        $fechasImpartidas = [];
        $fechasSuspendidas = [];
        $fechasFuturas = [];

        foreach ($reservas as $r) {
            $liberadosSet = DB::table('reserva_dias_liberados')
                ->where('id_reserva', $r->id_reserva)
                ->pluck('fecha')
                ->flip()
                ->all();
            foreach (self::fechasClaseReserva($r, $diasMap) as $fechaStr) {
                $fechasComprometidas[$fechaStr] = true;
                $lib = isset($liberadosSet[$fechaStr]);
                if ($lib) {
                    $fechasSuspendidas[$fechaStr] = true;
                } elseif ($fechaStr <= $hoyStr) {
                    $fechasImpartidas[$fechaStr] = true;
                } else {
                    $fechasFuturas[$fechaStr] = true;
                }
            }
        }

        $comprometidas = count($fechasComprometidas);
        $consumidas = count($fechasImpartidas);
        $suspendidas = count($fechasSuspendidas);
        $pendientesImpartir = count($fechasFuturas);
        $cupoLibreCalendario = max(0, $totalSesiones - $comprometidas);

        return [
            'total' => $totalSesiones,
            'consumidas' => $consumidas,
            'suspendidas' => $suspendidas,
            'restantes' => $pendientesImpartir,
            'cupo_libre_calendario' => $cupoLibreCalendario,
        ];
    }

    /**
     * Igual que calcularSesionesFichaCompetencia pero para un resultado de aprendizaje concreto
     * (total = resultados.sesiones de ese id; consumidas solo cuentan reservas con ese id_resultado).
     *
     * @return array{total: int, consumidas: int, suspendidas: int, restantes: int, cupo_libre_calendario: int}
     */
    public static function calcularSesionesFichaResultado(int $idFicha, ?int $idResultado): array
    {
        if (! $idResultado) {
            return ['total' => 0, 'consumidas' => 0, 'suspendidas' => 0, 'restantes' => 0, 'cupo_libre_calendario' => 0];
        }

        $totalSesiones = (int) DB::table('resultados')
            ->where('id_resultado', $idResultado)
            ->value('sesiones');

        $reservas = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_resultado', $idResultado)
            ->where('reservas.id_estado_reserva', 1)
            ->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')
            ->get();

        $diasMap = self::diasMapDow();
        $hoy = now()->startOfDay();
        $hoyStr = $hoy->format('Y-m-d');

        $fechasComprometidas = [];
        $fechasImpartidas = [];
        $fechasSuspendidas = [];
        $fechasFuturas = [];

        foreach ($reservas as $r) {
            $liberadosSet = DB::table('reserva_dias_liberados')
                ->where('id_reserva', $r->id_reserva)
                ->pluck('fecha')
                ->flip()
                ->all();
            foreach (self::fechasClaseReserva($r, $diasMap) as $fechaStr) {
                $fechasComprometidas[$fechaStr] = true;
                $lib = isset($liberadosSet[$fechaStr]);
                if ($lib) {
                    $fechasSuspendidas[$fechaStr] = true;
                } elseif ($fechaStr <= $hoyStr) {
                    $fechasImpartidas[$fechaStr] = true;
                } else {
                    $fechasFuturas[$fechaStr] = true;
                }
            }
        }

        $comprometidas = count($fechasComprometidas);
        $consumidas = count($fechasImpartidas);
        $suspendidas = count($fechasSuspendidas);
        $pendientesImpartir = count($fechasFuturas);
        $cupoLibreCalendario = max(0, $totalSesiones - $comprometidas);

        return [
            'total' => $totalSesiones,
            'consumidas' => $consumidas,
            'suspendidas' => $suspendidas,
            'restantes' => $pendientesImpartir,
            'cupo_libre_calendario' => $cupoLibreCalendario,
        ];
    }

    /**
     * True si existe una reserva activa de ese resultado con al menos un día liberado (recuperación).
     */
    public static function tieneLiberacionesParaResultado(int $idFicha, int $idCompetencia, int $idResultado): bool
    {
        return DB::table('reservas')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_competencia', $idCompetencia)
            ->where('reservas.id_resultado', $idResultado)
            ->where('reservas.id_estado_reserva', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('reserva_dias_liberados')
                    ->whereColumn('reserva_dias_liberados.id_reserva', 'reservas.id_reserva');
            })
            ->exists();
    }

    /**
     * True si algún resultado de la competencia aún tiene cupo libre en el calendario (para esta ficha).
     */
    public static function competenciaTieneCupoDisponibleEnAlgunResultado(int $idFicha, int $idCompetencia): bool
    {
        $ids = DB::table('resultados')->where('id_competencia', $idCompetencia)->pluck('id_resultado');
        if ($ids->isEmpty()) {
            return false;
        }
        foreach ($ids as $id) {
            $s = self::calcularSesionesFichaResultado($idFicha, (int) $id);
            if (($s['cupo_libre_calendario'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evalúa si el rango de fechas (más otras reservas activas del mismo resultado en la ficha)
     * supera el campo sesiones del resultado de aprendizaje.
     * Fechas INCLUSIVAS: primera y última fecha de clase del periodo.
     *
     * @return array{excede: bool, limite: int, total: int}
     */
    public static function evaluarSesionesResultado(
        int $idFicha,
        int $idResultado,
        string $fechaInicio,
        string $fechaFin,
        string $diaSemana,
        ?int $excluirReservaId = null
    ): array {
        $limite = (int) DB::table('resultados')
            ->where('id_resultado', $idResultado)
            ->value('sesiones');

        $diasMap = self::diasMapDow();
        $targetDow = $diasMap[strtolower(trim($diaSemana))] ?? null;
        if ($targetDow === null) {
            return ['excede' => false, 'limite' => $limite, 'total' => 0];
        }

        // Incluye días liberados: siguen ocupando cupo curricular del resultado.
        $todasLasFechas = [];

        $query = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_resultado', $idResultado)
            ->where('reservas.id_estado_reserva', 1);
        if ($excluirReservaId) {
            $query->where('reservas.id_reserva', '!=', $excluirReservaId);
        }
        $reservas = $query->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')
            ->get();

        foreach ($reservas as $r) {
            foreach (self::fechasClaseReserva($r, $diasMap) as $ymd) {
                $todasLasFechas[$ymd] = true;
            }
        }

        $inicio = \Carbon\Carbon::parse($fechaInicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($fechaFin)->startOfDay();
        $f = $inicio->copy();
        while ($f->lte($fin)) {
            if ($f->dayOfWeek === $targetDow) {
                $todasLasFechas[$f->format('Y-m-d')] = true;
            }
            $f->addDay();
        }

        $total = count($todasLasFechas);
        $excede = $limite > 0 && $total > $limite;

        return ['excede' => $excede, 'limite' => $limite, 'total' => $total];
    }

    /**
     * Evalúa cupo del resultado al agregar un conjunto explícito de fechas de sesión (Y-m-d),
     * sumando las fechas ya comprometidas por otras reservas activas del mismo resultado en la ficha.
     *
     * @param  list<string>  $nuevasFechasYmd
     * @return array{excede: bool, limite: int, total: int}
     */
    public static function evaluarSesionesResultadoAgregandoFechas(
        int $idFicha,
        int $idResultado,
        array $nuevasFechasYmd,
        ?int $excluirReservaId = null
    ): array {
        $limite = (int) DB::table('resultados')
            ->where('id_resultado', $idResultado)
            ->value('sesiones');

        $diasMap = self::diasMapDow();
        $todasLasFechas = [];

        $query = DB::table('reservas')
            ->join('dia_semana', 'reservas.id_dia_semana', '=', 'dia_semana.id_dia_semana')
            ->where('reservas.id_ficha', $idFicha)
            ->where('reservas.id_resultado', $idResultado)
            ->where('reservas.id_estado_reserva', 1);
        if ($excluirReservaId) {
            $query->where('reservas.id_reserva', '!=', $excluirReservaId);
        }
        $reservas = $query->select('reservas.id_reserva', 'reservas.fecha_inicio', 'reservas.fecha_fin', 'dia_semana.dia_semana')
            ->get();

        foreach ($reservas as $r) {
            foreach (self::fechasClaseReserva($r, $diasMap) as $ymd) {
                $todasLasFechas[$ymd] = true;
            }
        }
        foreach ($nuevasFechasYmd as $ymd) {
            if ($ymd !== '' && $ymd !== null) {
                $todasLasFechas[$ymd] = true;
            }
        }

        $total = count($todasLasFechas);
        $excede = $limite > 0 && $total > $limite;

        return ['excede' => $excede, 'limite' => $limite, 'total' => $total];
    }
}
