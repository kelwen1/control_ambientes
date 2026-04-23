<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Sincroniza el estado físico del ambiente con las reservas activas.
 */
class AmbienteReservaEstadoHelper
{
    /**
     * Actualiza el estado del ambiente según reservas activas (id_estado_reserva = 1).
     */
    public static function actualizarEstadoAmbiente(int $idAmbiente): void
    {
        $tieneReservasActivas = DB::table('reservas')
            ->where('id_ambiente', $idAmbiente)
            ->where('id_estado_reserva', 1)
            ->exists();

        if ($tieneReservasActivas) {
            DB::table('ambientes')
                ->where('id_ambiente', $idAmbiente)
                ->update(['id_estado' => 3]);
        } else {
            DB::table('ambientes')
                ->where('id_ambiente', $idAmbiente)
                ->update(['id_estado' => 1]);
        }
    }

    /**
     * Resuelve el id de estado "Finalizada" (o similar) en la tabla estado_reserva.
     */
    public static function idEstadoFinalizada(): ?int
    {
        $row = DB::table('estado_reserva')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre_estado) LIKE ?', ['%finaliz%'])
                    ->orWhereRaw('LOWER(nombre_estado) LIKE ?', ['%completad%']);
            })
            ->orderBy('id_estado_reserva')
            ->first();

        return $row ? (int) $row->id_estado_reserva : null;
    }
}
