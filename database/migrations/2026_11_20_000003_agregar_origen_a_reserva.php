<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reserva') || Schema::hasColumn('reserva', 'origen')) {
            return;
        }

        Schema::table('reserva', function (Blueprint $table): void {
            $table->string('origen', 20)->default('manual')->after('estado');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reserva') || ! Schema::hasColumn('reserva', 'origen')) {
            return;
        }

        Schema::table('reserva', function (Blueprint $table): void {
            $table->dropColumn('origen');
        });
    }
};
