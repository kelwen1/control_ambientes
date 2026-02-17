<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_Inventario');
            $table->unsignedBigInteger('id_ambiente');
            $table->integer('computadores')->default(0);
            $table->integer('sillas')->default(0);
            $table->integer('mesas')->default(0);
            $table->integer('aire_acondicionado')->default(0);
            $table->integer('tablero')->default(0);
            $table->integer('televisor')->default(0);
            $table->integer('ventiladores')->default(0);
            $table->integer('vidiovid')->default(0);
            $table->integer('herramientas')->default(0);
            
            $table->foreign('id_ambiente')->references('id_ambiente')->on('ambientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
