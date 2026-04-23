<?php

namespace App\Console\Commands;

use App\Helpers\FichaProgramaDuracionHelper;
use App\Models\Duracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SincronizarDuracionNivelesCommand extends Command
{
    protected $signature = 'niveles:sincronizar-duracion {--force : Actualizar también filas que ya tienen id_duracion}';

    protected $description = 'Rellena nivel_programa.id_duracion cuando falta, usando reglas por nombre (tecnología, técnica, etc.). Los nombres personalizados sin regla deben editarse en la interfaz.';

    public function handle(): int
    {
        if (! Schema::hasTable('nivel_programa') || ! Schema::hasColumn('nivel_programa', 'id_duracion')) {
            $this->error('La tabla nivel_programa no tiene la columna id_duracion. Ejecute: php artisan migrate');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $rows = DB::table('nivel_programa')->get(['id_nivel_programa', 'nivel_programa', 'id_duracion']);
        $actualizados = 0;

        foreach ($rows as $row) {
            if (! $force && $row->id_duracion !== null) {
                continue;
            }

            $meses = FichaProgramaDuracionHelper::mesesPorNombreNivel((string) $row->nivel_programa);
            if ($meses === null) {
                continue;
            }

            $idDur = Duracion::idForMeses($meses);
            DB::table('nivel_programa')
                ->where('id_nivel_programa', $row->id_nivel_programa)
                ->update(['id_duracion' => $idDur]);
            $this->line(sprintf(
                'Nivel %d (%s) → id_duracion %d (%d meses)',
                $row->id_nivel_programa,
                $row->nivel_programa,
                $idDur,
                $meses
            ));
            $actualizados++;
        }

        if ($actualizados === 0) {
            $this->warn('No se actualizó ningún nivel (sin regla por nombre o ya vinculados). Edite niveles personalizados en «Niveles de programa» y guarde los meses.');
        } else {
            $this->info("Listo: {$actualizados} nivel(es) actualizado(s).");
        }

        return self::SUCCESS;
    }
}
