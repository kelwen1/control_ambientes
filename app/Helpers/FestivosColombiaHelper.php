<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Días festivos oficiales de Colombia.
 * Fuente: festivos.com.co (Ley Emiliani aplicada).
 *
 * @return array{fecha: string, nombre: string, dia_semana: int}[]
 */
class FestivosColombiaHelper
{
    /** Carbon: 0=Domingo, 1=Lunes, ..., 6=Sábado */
    private const FESTIVOS = [
        2025 => [
            ['fecha' => '2025-01-01', 'nombre' => 'Año Nuevo'],
            ['fecha' => '2025-01-06', 'nombre' => 'Reyes Magos'],
            ['fecha' => '2025-03-24', 'nombre' => 'Día de San José'],
            ['fecha' => '2025-04-17', 'nombre' => 'Jueves Santo'],
            ['fecha' => '2025-04-18', 'nombre' => 'Viernes Santo'],
            ['fecha' => '2025-05-01', 'nombre' => 'Día del Trabajo'],
            ['fecha' => '2025-06-02', 'nombre' => 'Ascensión del Señor'],
            ['fecha' => '2025-06-23', 'nombre' => 'Corpus Christi'],
            ['fecha' => '2025-06-30', 'nombre' => 'Sagrado Corazón / San Pedro y San Pablo'],
            ['fecha' => '2025-07-20', 'nombre' => 'Día de la Independencia'],
            ['fecha' => '2025-08-07', 'nombre' => 'Batalla de Boyacá'],
            ['fecha' => '2025-08-18', 'nombre' => 'Asunción de la Virgen'],
            ['fecha' => '2025-10-13', 'nombre' => 'Día de la Raza'],
            ['fecha' => '2025-11-03', 'nombre' => 'Todos los Santos'],
            ['fecha' => '2025-11-17', 'nombre' => 'Independencia de Cartagena'],
            ['fecha' => '2025-12-08', 'nombre' => 'Inmaculada Concepción'],
            ['fecha' => '2025-12-25', 'nombre' => 'Navidad'],
        ],
        2026 => [
            ['fecha' => '2026-01-01', 'nombre' => 'Año Nuevo'],
            ['fecha' => '2026-01-12', 'nombre' => 'Reyes Magos'],
            ['fecha' => '2026-03-23', 'nombre' => 'Día de San José'],
            ['fecha' => '2026-04-02', 'nombre' => 'Jueves Santo'],
            ['fecha' => '2026-04-03', 'nombre' => 'Viernes Santo'],
            ['fecha' => '2026-05-01', 'nombre' => 'Día del Trabajo'],
            ['fecha' => '2026-05-18', 'nombre' => 'Ascensión del Señor'],
            ['fecha' => '2026-06-08', 'nombre' => 'Corpus Christi'],
            ['fecha' => '2026-06-15', 'nombre' => 'Sagrado Corazón de Jesús'],
            ['fecha' => '2026-06-29', 'nombre' => 'San Pedro y San Pablo'],
            ['fecha' => '2026-07-20', 'nombre' => 'Día de la Independencia'],
            ['fecha' => '2026-08-07', 'nombre' => 'Batalla de Boyacá'],
            ['fecha' => '2026-08-17', 'nombre' => 'Asunción de la Virgen'],
            ['fecha' => '2026-10-12', 'nombre' => 'Día de la Raza'],
            ['fecha' => '2026-11-02', 'nombre' => 'Todos los Santos'],
            ['fecha' => '2026-11-16', 'nombre' => 'Independencia de Cartagena'],
            ['fecha' => '2026-12-08', 'nombre' => 'Inmaculada Concepción'],
            ['fecha' => '2026-12-25', 'nombre' => 'Navidad'],
        ],
        2027 => [
            ['fecha' => '2027-01-01', 'nombre' => 'Año Nuevo'],
            ['fecha' => '2027-01-11', 'nombre' => 'Reyes Magos'],
            ['fecha' => '2027-03-22', 'nombre' => 'Día de San José'],
            ['fecha' => '2027-03-25', 'nombre' => 'Jueves Santo'],
            ['fecha' => '2027-03-26', 'nombre' => 'Viernes Santo'],
            ['fecha' => '2027-05-01', 'nombre' => 'Día del Trabajo'],
            ['fecha' => '2027-05-10', 'nombre' => 'Ascensión del Señor'],
            ['fecha' => '2027-05-31', 'nombre' => 'Corpus Christi'],
            ['fecha' => '2027-06-07', 'nombre' => 'Sagrado Corazón de Jesús'],
            ['fecha' => '2027-07-05', 'nombre' => 'San Pedro y San Pablo'],
            ['fecha' => '2027-07-20', 'nombre' => 'Día de la Independencia'],
            ['fecha' => '2027-08-07', 'nombre' => 'Batalla de Boyacá'],
            ['fecha' => '2027-08-16', 'nombre' => 'Asunción de la Virgen'],
            ['fecha' => '2027-10-18', 'nombre' => 'Día de la Raza'],
            ['fecha' => '2027-11-01', 'nombre' => 'Todos los Santos'],
            ['fecha' => '2027-11-15', 'nombre' => 'Independencia de Cartagena'],
            ['fecha' => '2027-12-08', 'nombre' => 'Inmaculada Concepción'],
            ['fecha' => '2027-12-25', 'nombre' => 'Navidad'],
        ],
    ];

    /**
     * Obtiene todos los festivos de Colombia para uno o más años.
     *
     * @param  int  ...$anios  Años a consultar (ej: 2025, 2026)
     * @return array{fecha: string, nombre: string, dia_semana: int}[]
     */
    public static function obtenerFestivos(int ...$anios): array
    {
        $resultado = [];
        foreach ($anios as $anio) {
            $lista = self::FESTIVOS[$anio] ?? [];
            foreach ($lista as $f) {
                $carbon = Carbon::parse($f['fecha']);
                $resultado[] = [
                    'fecha' => $f['fecha'],
                    'nombre' => $f['nombre'],
                    'dia_semana' => $carbon->dayOfWeek, // 0=Dom, 1=Lun, ..., 6=Sab
                ];
            }
        }

        return $resultado;
    }

    /**
     * Festivos que caen dentro del rango de fechas (inclusivo) y coinciden con el día de la semana.
     *
     * @param  string  $fechaInicio  Y-m-d (inclusivo, primera clase)
     * @param  string  $fechaFin  Y-m-d (inclusivo, última clase)
     * @param  int  $diaSemana  0=Dom, 1=Lun, ..., 6=Sab (Carbon dayOfWeek)
     * @return array{fecha: string, nombre: string}[]
     */
    public static function festivosEnRango(string $fechaInicio, string $fechaFin, int $diaSemana): array
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $inicioStr = $inicio->format('Y-m-d');
        $finStr = $fin->format('Y-m-d');
        $anioInicio = (int) $inicio->year;
        $anioFin = (int) $fin->year;
        $anios = range($anioInicio, $anioFin);

        $festivos = self::obtenerFestivos(...$anios);
        $enRango = [];
        foreach ($festivos as $f) {
            if ($f['dia_semana'] !== $diaSemana) {
                continue;
            }
            $fecha = $f['fecha'];
            if ($fecha >= $inicioStr && $fecha <= $finStr) {
                $enRango[] = ['fecha' => $fecha, 'nombre' => $f['nombre']];
            }
        }
        usort($enRango, fn ($a, $b) => strcmp($a['fecha'], $b['fecha']));

        return $enRango;
    }
}
