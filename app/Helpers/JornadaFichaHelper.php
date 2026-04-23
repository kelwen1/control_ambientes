<?php

namespace App\Helpers;

use Carbon\Carbon;

class JornadaFichaHelper
{
    /**
     * Fin de semana en reservas: id_jornada = 4 (fin_semana).
     * Lunes a viernes: 1 = mañana, 2 = tarde, 3 = noche.
     */
    public static function fechaInicioCompatibleConJornadaFicha(string $fechaYmd, int $idJornada): bool
    {
        $d = Carbon::parse($fechaYmd)->startOfDay();
        $esFinDeSemana = $d->isSaturday() || $d->isSunday();

        if ($esFinDeSemana) {
            return $idJornada === 4;
        }

        return in_array($idJornada, [1, 2, 3], true);
    }

    /**
     * Inicio y fin del rango deben cumplir la misma regla: L–V para jornadas 1–3; sábado o domingo para jornada 4.
     */
    public static function rangoFechasCompatibleConJornadaFicha(string $fechaInicio, string $fechaFin, int $idJornada): bool
    {
        return self::fechaInicioCompatibleConJornadaFicha($fechaInicio, $idJornada)
            && self::fechaInicioCompatibleConJornadaFicha($fechaFin, $idJornada);
    }
}
