<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuarios') || Schema::hasColumn('usuarios', 'id_encargado')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->unsignedInteger('id_encargado')->nullable()->after('rol');

            $table->foreign('id_encargado')
                ->references('id_encargado')
                ->on('encargado_plato')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasColumn('usuarios', 'id_encargado')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropForeign(['id_encargado']);
            $table->dropColumn('id_encargado');
        });
    }
};
