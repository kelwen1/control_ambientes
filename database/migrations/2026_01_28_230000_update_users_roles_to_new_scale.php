<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Escala de roles actualizada:
     * 1 = Administrador (máximo poder)
     * 2 = Coordinador
     * 3 = Usuario (menor poder)
     *
     * Los usuarios que tenían id_rol = 2 (antes "Usuario") pasan a id_rol = 3.
     * Los coordinadores se asignarán manualmente desde el panel (id_rol = 2).
     */
    public function up(): void
    {
        DB::table('users')
            ->where('id_rol', 2)
            ->update(['id_rol' => 3]);
    }

    /**
     * Reverse the migrations.
     *
     * Revertir: quienes tienen 3 (Usuario) vuelven a 2 (comportamiento anterior).
     */
    public function down(): void
    {
        DB::table('users')
            ->where('id_rol', 3)
            ->update(['id_rol' => 2]);
    }
};
