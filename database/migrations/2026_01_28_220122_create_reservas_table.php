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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id('id_reserva');
            $table->unsignedBigInteger('id_ambiente');
            $table->unsignedBigInteger('id_ficha');
            $table->string('dia_semana'); // 'lunes' o 'sabado'
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedBigInteger('id_estado_reserva')->default(1); // 1=Activa, 2=Cancelada, 3=Finalizada
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('id_ambiente')->references('id_ambiente')->on('ambientes');
            $table->foreign('id_ficha')->references('id_ficha')->on('ficha');
            $table->foreign('id_estado_reserva')->references('id_estado_reserva')->on('estado_reserva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
