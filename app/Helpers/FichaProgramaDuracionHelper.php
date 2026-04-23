<?php

namespace App\Helpers;

use App\Models\Programa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Duración de formación según nivel SENA (fechas de ficha).
 * Preferencia: id_duracion en nivel_programa → tabla duracion.
 * Respaldo: reglas por palabras en el nombre (media técnica / técnica / tecnología).
 */
class FichaProgramaDuracionHelper
{
    /**
     * Meses a partir del id_duracion de la tabla duracion.
     */
    public static function mesesPorIdDuracion(?int $idDuracion): ?int
    {
        if ($idDuracion === null) {
            return null;
        }

        $dur = DB::table('duracion')->where('id_duracion', $idDuracion)->first();
        if (! $dur || ! preg_match('/(\d+)/u', (string) $dur->duracion, $m)) {
            return null;
        }

        return (int) $m[1];
    }

    /**
     * Meses para fechas de ficha: prioriza id_duracion del programa, luego reglas por nivel.
     */
    public static function mesesParaPrograma(Programa $programa): ?int
    {
        $m = self::mesesPorIdDuracion($programa->id_duracion ?? null);
        if ($m !== null) {
            return $m;
        }

        return self::mesesPorNivelId($programa->id_nivel_programa);
    }

    /**
     * @return int|null Meses de duración o null si el nivel no es reconocible
     */
    public static function mesesPorNivelId(?int $idNivel): ?int
    {
        if ($idNivel === null) {
            return null;
        }

        $row = DB::table('nivel_programa')
            ->where('id_nivel_programa', $idNivel)
            ->first();

        if (! $row || ! isset($row->nivel_programa)) {
            return null;
        }

        if (Schema::hasColumn('nivel_programa', 'id_duracion')
            && isset($row->id_duracion)
            && $row->id_duracion !== null) {
            $dur = DB::table('duracion')->where('id_duracion', $row->id_duracion)->first();
            if ($dur && preg_match('/(\d+)/u', (string) $dur->duracion, $m)) {
                return (int) $m[1];
            }
        }

        return self::mesesPorNombreNivel((string) $row->nivel_programa);
    }

    public static function mesesPorNombreNivel(string $nombre): ?int
    {
        $n = mb_strtolower(trim($nombre), 'UTF-8');
        $n = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $n);

        $tieneMedia = str_contains($n, 'media');
        // "tecnologial" contiene "tecnolog" pero no es la regla SENA de tecnología (24 meses).
        // Debe resolverse antes que la comprobación genérica por subcadena "tecnolog".
        if (str_contains($n, 'tecnologial')) {
            return 27;
        }
        // Variante ortográfica frecuente en nombres personalizados (p. ej. "tegnologial")
        if (str_contains($n, 'tegnolog')) {
            return 27;
        }
        // "tecnologia" contiene "tecnica": evaluar tecnología antes que técnica suelta
        $tieneTecnologia = str_contains($n, 'tecnolog');
        $tieneTecnica = str_contains($n, 'tecnica');

        if ($tieneMedia && $tieneTecnica && ! $tieneTecnologia) {
            return 12;
        }

        if ($tieneTecnologia) {
            return 24;
        }

        if ($tieneTecnica) {
            return 18;
        }

        return null;
    }

    /**
     * Texto de duración y meses según el nombre del nivel (solo por texto; uso residual).
     *
     * @return array{meses: int, etiqueta: string, id_duracion: int|null}|null
     */
    public static function resumenDuracionPorNombreNivel(string $nombre): ?array
    {
        $meses = self::mesesPorNombreNivel($nombre);
        if ($meses === null) {
            return null;
        }

        $etiqueta = $meses.' meses';
        $idDuracion = null;
        foreach (DB::table('duracion')->get(['id_duracion', 'duracion']) as $row) {
            if (preg_match('/(\d+)/u', (string) $row->duracion, $m) && (int) $m[1] === $meses) {
                $idDuracion = (int) $row->id_duracion;
                $etiqueta = (string) $row->duracion;

                break;
            }
        }

        return [
            'meses' => $meses,
            'etiqueta' => $etiqueta,
            'id_duracion' => $idDuracion,
        ];
    }

    /**
     * id_duracion en tabla duracion según el nivel.
     */
    public static function idDuracionPorNivelId(?int $idNivel): ?int
    {
        if ($idNivel === null) {
            return null;
        }

        $row = DB::table('nivel_programa')
            ->where('id_nivel_programa', $idNivel)
            ->first();

        if (! $row) {
            return null;
        }

        if (Schema::hasColumn('nivel_programa', 'id_duracion')
            && isset($row->id_duracion)
            && $row->id_duracion !== null) {
            return (int) $row->id_duracion;
        }

        $meses = self::mesesPorNombreNivel((string) $row->nivel_programa);
        if ($meses === null) {
            return null;
        }

        $rows = DB::table('duracion')->get(['id_duracion', 'duracion']);
        foreach ($rows as $d) {
            if (preg_match('/(\d+)/u', (string) $d->duracion, $m) && (int) $m[1] === $meses) {
                return (int) $d->id_duracion;
            }
        }

        return null;
    }
}
