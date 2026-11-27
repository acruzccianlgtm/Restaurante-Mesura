<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categoria')) {
            return;
        }

        DB::table('categoria')->where('nombre', 'Pavo')->update(['nombre' => 'Carnes']);
        DB::table('categoria')->where('nombre', 'Bebidas')->update(['nombre' => 'Sopas']);

        $idEncargado = Schema::hasTable('encargado_plato')
            ? DB::table('encargado_plato')->min('id_encargado')
            : null;

        if ($idEncargado !== null && ! DB::table('categoria')->where('nombre', 'Vegetales')->exists()) {
            DB::table('categoria')->insert([
                'nombre' => 'Vegetales',
                'id_encargado' => $idEncargado,
                'estado' => 'activo',
            ]);
        }

        if (Schema::hasTable('encargado_plato') && ! Schema::hasColumn('encargado_plato', 'id_categoria')) {
            Schema::table('encargado_plato', function (Blueprint $table): void {
                $table->unsignedInteger('id_categoria')->nullable()->after('cargo');
                $table->foreign('id_categoria')->references('id_categoria')->on('categoria')->nullOnDelete();
            });

            foreach (DB::table('categoria')->select('id_encargado', DB::raw('MIN(id_categoria) AS id_categoria'))->groupBy('id_encargado')->get() as $fila) {
                DB::table('encargado_plato')
                    ->where('id_encargado', $fila->id_encargado)
                    ->update(['id_categoria' => $fila->id_categoria]);
            }
        }

        if (Schema::hasTable('usuarios') && ! Schema::hasColumn('usuarios', 'id_categoria')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->unsignedInteger('id_categoria')->nullable()->after('rol');
                $table->foreign('id_categoria')->references('id_categoria')->on('categoria')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'id_categoria')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->dropForeign(['id_categoria']);
                $table->dropColumn('id_categoria');
            });
        }

        if (Schema::hasTable('encargado_plato') && Schema::hasColumn('encargado_plato', 'id_categoria')) {
            Schema::table('encargado_plato', function (Blueprint $table): void {
                $table->dropForeign(['id_categoria']);
                $table->dropColumn('id_categoria');
            });
        }

        if (! Schema::hasTable('categoria')) {
            return;
        }

        DB::table('categoria')->where('nombre', 'Carnes')->update(['nombre' => 'Pavo']);
        DB::table('categoria')->where('nombre', 'Sopas')->update(['nombre' => 'Bebidas']);
        DB::table('categoria')->where('nombre', 'Vegetales')->delete();
    }
};
