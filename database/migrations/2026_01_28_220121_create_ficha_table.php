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
        Schema::create('ficha', function (Blueprint $table) {
            $table->id('id_ficha');
            $table->string('num_ficha');
            $table->unsignedInteger('cant_aprendices')->default(1);
            $table->unsignedBigInteger('id_programa');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_productiva')->nullable();
            $table->foreign('id_programa')->references('id_programa')->on('programa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ficha');
    }
};
