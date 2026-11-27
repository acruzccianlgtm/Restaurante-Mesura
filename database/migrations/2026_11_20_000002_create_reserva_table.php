<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reserva')) {
            return;
        }

        Schema::create('reserva', function (Blueprint $table): void {
            $table->increments('id_reserva');
            $table->unsignedInteger('id_mesa');
            $table->string('nombre_cliente', 120);
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_reserva');
            $table->time('hora_reserva');
            $table->unsignedInteger('cantidad_personas');
            $table->string('estado', 20)->default('activa');

            $table->foreign('id_mesa')->references('id_mesa')->on('mesa')->cascadeOnDelete();
            $table->index('id_mesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
