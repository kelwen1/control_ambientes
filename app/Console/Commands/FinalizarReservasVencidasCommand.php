<?php

namespace App\Console\Commands;

use App\Helpers\AmbienteReservaEstadoHelper;
use App\Helpers\ReservaFechasClaseHelper;
use App\Models\Reserva;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinalizarReservasVencidasCommand extends Command
{
    protected $signature = 'reservas:finalizar-vencidas';

    protected $description = 'Marca como finalizadas las reservas activas cuya última fecha de clase ya pasó, y actualiza el estado de los ambientes.';

    public function handle(): int
    {
        $idFinal = AmbienteReservaEstadoHelper::idEstadoFinalizada();
        if ($idFinal === null) {
            $this->warn('No se encontró un estado "Finalizada" en estado_reserva. Configure el catálogo o el nombre esperado.');

            return self::FAILURE;
        }

        $activa = 1;
        $reservas = Reserva::query()
            ->where('id_estado_reserva', $activa)
            ->get();

        $n = 0;
        $ambientesTocados = [];

        foreach ($reservas as $r) {
            $dia = DB::table('dia_semana')->where('id_dia_semana', $r->id_dia_semana)->value('dia_semana');
            $fc = ReservaFechasClaseHelper::fechasClaseEnRangoInclusivo(
                $r->fecha_inicio,
                $r->fecha_fin,
                $dia ? (string) $dia : null
            );
            $ultima = $fc['ultima'] ?? null;
            if ($ultima === null) {
                continue;
            }
            if (\Carbon\Carbon::parse($ultima)->startOfDay()->lt(now()->startOfDay())) {
                $r->id_estado_reserva = $idFinal;
                $r->save();
                $n++;
                $ambientesTocados[(int) $r->id_ambiente] = true;
            }
        }

        foreach (array_keys($ambientesTocados) as $idAmb) {
            AmbienteReservaEstadoHelper::actualizarEstadoAmbiente($idAmb);
        }

        if ($n > 0) {
            $this->info("Reservas finalizadas: {$n}.");
        } else {
            $this->info('No había reservas activas vencidas por fecha de última clase.');
        }

        return self::SUCCESS;
    }
}
