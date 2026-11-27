<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table): void {
                if (! Schema::hasColumn('empleados', 'estado')) {
                    $table->string('estado', 10)->default('activo')->after('edad');
                }
            });
        }

        if (Schema::hasTable('encargado_plato')) {
            Schema::table('encargado_plato', function (Blueprint $table): void {
                if (! Schema::hasColumn('encargado_plato', 'edad')) {
                    $table->integer('edad')->nullable()->after('apellido');
                }

                if (! Schema::hasColumn('encargado_plato', 'ciudad')) {
                    $table->string('ciudad', 40)->nullable()->after('telefono');
                }
            });
        }

        if (Schema::hasTable('usuarios')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                if (! Schema::hasColumn('usuarios', 'nombre')) {
                    $table->string('nombre', 80)->nullable()->after('correo');
                }

                if (! Schema::hasColumn('usuarios', 'apellido')) {
                    $table->string('apellido', 80)->nullable()->after('nombre');
                }

                if (! Schema::hasColumn('usuarios', 'edad')) {
                    $table->integer('edad')->nullable()->after('apellido');
                }

                if (! Schema::hasColumn('usuarios', 'telefono')) {
                    $table->string('telefono', 20)->nullable()->after('edad');
                }

                if (! Schema::hasColumn('usuarios', 'ciudad')) {
                    $table->string('ciudad', 40)->nullable()->after('telefono');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('empleados') && Schema::hasColumn('empleados', 'estado')) {
            Schema::table('empleados', function (Blueprint $table): void {
                $table->dropColumn('estado');
            });
        }

        if (Schema::hasTable('encargado_plato')) {
            Schema::table('encargado_plato', function (Blueprint $table): void {
                if (Schema::hasColumn('encargado_plato', 'edad')) {
                    $table->dropColumn('edad');
                }

                if (Schema::hasColumn('encargado_plato', 'ciudad')) {
                    $table->dropColumn('ciudad');
                }
            });
        }

        if (Schema::hasTable('usuarios')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                foreach (['nombre', 'apellido', 'edad', 'telefono', 'ciudad'] as $columna) {
                    if (Schema::hasColumn('usuarios', $columna)) {
                        $table->dropColumn($columna);
                    }
                }
            });
        }
    }
};
