<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ingrediente')) {
            Schema::create('ingrediente', function (Blueprint $table) {
                $table->increments('id_ingrediente');
                $table->string('nombre', 120)->unique();
                $table->string('unidad_medida_almacen', 20);
                $table->decimal('cantidad_actual_almacen', 10, 2)->default(0);
                $table->decimal('cantidad_minima', 10, 2)->default(0);
                $table->string('estado', 10)->default('activo');
            });

            return;
        }

        if (! Schema::hasColumn('ingrediente', 'cantidad_minima')) {
            Schema::table('ingrediente', function (Blueprint $table) {
                $table->decimal('cantidad_minima', 10, 2)->default(0)->after('cantidad_actual_almacen');
            });
        }

        if (! Schema::hasColumn('ingrediente', 'estado')) {
            Schema::table('ingrediente', function (Blueprint $table) {
                $table->string('estado', 10)->default('activo')->after('cantidad_minima');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ingrediente')) {
            return;
        }

        Schema::table('ingrediente', function (Blueprint $table) {
            if (Schema::hasColumn('ingrediente', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('ingrediente', 'cantidad_minima')) {
                $table->dropColumn('cantidad_minima');
            }
        });
    }
};
