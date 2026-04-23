<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Clave de día de semana alineada con la tabla dia_semana y Carbon::dayOfWeek (0 = domingo).
 */
class DiaSemanaDesdeFechaHelper
{
    /** @var array<int, string> */
    private const DOW_TO_CLAVE = [
        0 => 'domingo',
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
    ];

    public static function claveDesdeYmd(?string $ymd): ?string
    {
        if ($ymd === null || $ymd === '') {
            return null;
        }
        try {
            $dow = Carbon::parse($ymd)->dayOfWeek;
        } catch (\Throwable) {
            return null;
        }

        return self::DOW_TO_CLAVE[$dow] ?? null;
    }
}
