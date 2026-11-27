<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('encargado_plato') || ! Schema::hasTable('categoria')) {
            return;
        }

        $idCategoria = DB::table('categoria')->where('nombre', 'Vegetales')->value('id_categoria');

        if ($idCategoria !== null) {
            DB::table('encargado_plato')
                ->whereNull('id_categoria')
                ->update(['id_categoria' => $idCategoria]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('encargado_plato')) {
            return;
        }

        DB::table('encargado_plato')->update(['id_categoria' => null]);
    }
};
