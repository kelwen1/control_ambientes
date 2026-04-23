<?php

namespace App\Helpers;

use Carbon\Carbon;

class ReservaPlanFechasHelper
{
    /**
     * Días de la semana (clave) permitidos según jornada de la ficha.
     * 1–3: lunes a viernes; 4: sábado y domingo.
     *
     * @return list<string>
     */
    public static function diasSemanaPermitidosPorJornada(int $idJornada): array
    {
        if ($idJornada === 4) {
            return ['sabado', 'domingo'];
        }

        return ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    }

    /**
     * Genera todas las fechas Y-m-d entre inicio y fin (inclusivo) que:
     * - caen en uno de los días de la semana seleccionados, y
     * - cumplen la regla de jornada (L–V vs fin de semana).
     *
     * @param  list<string>  $diasClavesSeleccionados  ej. ['lunes','miercoles']
     * @return list<string> fechas ordenadas
     */
    public static function generarFechasSesion(string $fechaInicio, string $fechaFin, array $diasClavesSeleccionados, int $idJornada): array
    {
        $permitidos = self::diasSemanaPermitidosPorJornada($idJornada);
        $permitidosFlip = array_flip($permitidos);

        $diasSet = [];
        foreach ($diasClavesSeleccionados as $d) {
            $k = strtolower(trim((string) $d));
            if (isset($permitidosFlip[$k])) {
                $diasSet[$k] = true;
            }
        }

        if ($diasSet === []) {
            return [];
        }

        $map = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $dowTargets = [];
        foreach (array_keys($diasSet) as $d) {
            if (isset($map[$d])) {
                $dowTargets[$map[$d]] = true;
            }
        }

        $ini = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $out = [];
        $f = $ini->copy();
        while ($f->lte($fin)) {
            $ymd = $f->format('Y-m-d');
            if (isset($dowTargets[$f->dayOfWeek])
                && JornadaFichaHelper::fechaInicioCompatibleConJornadaFicha($ymd, $idJornada)) {
                $out[$ymd] = true;
            }
            $f->addDay();
        }

        $keys = array_keys($out);
        sort($keys);

        return $keys;
    }
}
