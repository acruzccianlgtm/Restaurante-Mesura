<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        DB::statement("ALTER TABLE `usuarios` MODIFY `rol` ENUM('cliente', 'encargado', 'mesero', 'jefe de cocina', 'cocinero', 'cajero') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        DB::statement("UPDATE `usuarios` SET `rol` = NULL WHERE `rol` = 'cajero'");
        DB::statement("ALTER TABLE `usuarios` MODIFY `rol` ENUM('cliente', 'encargado', 'mesero', 'jefe de cocina', 'cocinero') NULL DEFAULT NULL");
    }
};
