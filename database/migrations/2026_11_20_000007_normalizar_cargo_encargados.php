<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('encargado_plato')) {
            return;
        }

        DB::table('encargado_plato')->update(['cargo' => 'Encargado de categoria']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('encargado_plato')) {
            return;
        }

        DB::table('encargado_plato')->update(['cargo' => null]);
    }
};
