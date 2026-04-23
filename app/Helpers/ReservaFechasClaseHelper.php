<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Fechas reales de clase dentro del rango guardado en reservas.
 * fecha_inicio y fecha_fin son INCLUSIVOS: primera y última fecha de clase del periodo.
 * Usa el mismo criterio que ReservasController::hayConflictoReserva (dayOfWeek, domingo = 0).
 */
class ReservaFechasClaseHelper
{
    /** @var array<string, int> */
    private const DOW_MAP = [
        'lunes' => 1,
        'martes' => 2,
        'miercoles' => 3,
        'jueves' => 4,
        'viernes' => 5,
        'sabado' => 6,
        'domingo' => 0,
    ];

    /**
     * @return array{
     *   fechas: string[],
     *   primera: ?string,
     *   ultima: ?string,
     *   labelPrimera: ?string,
     *   labelUltima: ?string
     * }
     */
    public static function fechasClaseEnRangoInclusivo(?string $fechaInicio, ?string $fechaFin, ?string $diaSemanaDb): array
    {
        $vacío = ['fechas' => [], 'primera' => null, 'ultima' => null, 'labelPrimera' => null, 'labelUltima' => null];

        if (! $fechaInicio || ! $fechaFin) {
            return $vacío;
        }

        $diaTexto = strtolower(trim((string) $diaSemanaDb));
        $dowTarget = self::DOW_MAP[$diaTexto] ?? null;
        if ($dowTarget === null) {
            return $vacío;
        }

        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $fechas = [];

        $fecha = $inicio->copy()->startOfDay();
        $finD = $fin->copy()->startOfDay();
        while ($fecha->lte($finD)) {
            if ((int) $fecha->dayOfWeek === $dowTarget) {
                $fechas[] = $fecha->format('Y-m-d');
            }
            $fecha->addDay();
        }

        $primera = $fechas[0] ?? null;
        $ultima = count($fechas) > 0 ? $fechas[count($fechas) - 1] : null;

        return [
            'fechas' => $fechas,
            'primera' => $primera,
            'ultima' => $ultima,
            'labelPrimera' => $primera ? Carbon::parse($primera)->format('d/m/Y') : null,
            'labelUltima' => $ultima ? Carbon::parse($ultima)->format('d/m/Y') : null,
        ];
    }

    /**
     * True si queda al menos una fecha de clase estrictamente posterior a hoy (inicio del día en la app).
     */
    public static function tieneAlgunaClaseFutura(?string $fechaInicio, ?string $fechaFin, ?string $diaSemanaDb): bool
    {
        $fc = self::fechasClaseEnRangoInclusivo($fechaInicio, $fechaFin, $diaSemanaDb);
        $hoy = now()->startOfDay()->format('Y-m-d');
        foreach ($fc['fechas'] as $ymd) {
            if ($ymd > $hoy) {
                return true;
            }
        }

        return false;
    }
}
