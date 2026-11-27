<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tablas = ['usuarios', 'encargado_plato', 'plato', 'categoria'];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            if (! Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'estado')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $blueprint): void {
                $blueprint->string('estado', 10)->default('activo');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'estado')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('estado');
            });
        }
    }
};
