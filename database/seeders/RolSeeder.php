<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Seed los 4 roles del sistema.
     */
    public function run(): void
    {
        DB::table('rol')->insertOrIgnore([
            ['id_rol' => 1, 'rol' => 'administrador'],
            ['id_rol' => 2, 'rol' => 'coordinacion_L'],
            ['id_rol' => 3, 'rol' => 'coordinacion'],
            ['id_rol' => 4, 'rol' => 'instructor'],
        ]);
    }
}
