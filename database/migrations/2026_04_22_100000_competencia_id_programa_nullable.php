<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las competencias pasan a ser de catálogo común: no se arraigan a un solo programa.
     * Cualquier ficha (por su programa) puede usarlas en reservas.
     */
    public function up(): void
    {
        if (! Schema::hasTable('competencia') || ! Schema::hasColumn('competencia', 'id_programa')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE `competencia` MODIFY `id_programa` INT UNSIGNED NULL');
            } catch (\Throwable) {
                try {
                    DB::statement('ALTER TABLE `competencia` MODIFY `id_programa` INT NULL');
                } catch (\Throwable) {
                    // Tipo de columna distinto: intentar con lo más habitual
                    DB::statement('ALTER TABLE `competencia` MODIFY `id_programa` BIGINT UNSIGNED NULL');
                }
            }
        }
        // SQLite: en pruebas suele recrearse el esquema; si hace falta, migrar a mano.

        DB::table('competencia')->update(['id_programa' => null]);
    }

    public function down(): void
    {
        // No se revierte a NOT NULL con datos NUlL existentes
    }
};
