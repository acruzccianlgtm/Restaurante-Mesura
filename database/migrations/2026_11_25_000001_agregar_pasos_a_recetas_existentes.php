<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pasos de preparación de los platillos que ya estaban registrados.
     *
     * @var array<string, string>
     */
    private array $pasosPorPlato = [
        'Ceviche de camaron' => "Lavar y pelar los camarones, retirando la vena intestinal.\nCocerlos en agua hirviendo con sal durante 2 minutos y enfriarlos.\nCortarlos en trozos y marinarlos con limón, cilantro, cebolla y sal.\nRefrigerar 15 minutos antes de servir.",
        'Pavo horneado' => "Precalentar el horno a 180 °C.\nLimpiar y secar el pavo, sazonarlo con sal, pimienta y mantequilla.\nColocarlo en una bandeja y hornearlo 90 minutos, bañándolo con su jugo cada 20 minutos.\nDejar reposar 10 minutos antes de cortar y servir.",
        'Tarta de chocolate' => "Derretir el chocolate a baño maría.\nMezclarlo con mantequilla, azúcar y huevos hasta obtener una masa homogénea.\nVerter la masa en un molde y hornear a 180 °C durante 25 minutos.\nEnfriar antes de desmoldar y decorar.",
        'Limonada natural' => "Lavar los limones y extraer el jugo.\nColar el jugo para retirar semillas y pulpa.\nMezclarlo con agua fría y azúcar al gusto.\nServir con hielo y rodajas de limón.",
    ];

    public function up(): void
    {
        if (! Schema::hasTable('plato') || ! Schema::hasTable('receta')) {
            return;
        }

        $ids = DB::table('plato')
            ->whereIn('nombre', array_keys($this->pasosPorPlato))
            ->pluck('id_plato', 'nombre');

        foreach ($this->pasosPorPlato as $nombre => $pasos) {
            if (! isset($ids[$nombre])) {
                continue;
            }

            DB::table('receta')
                ->where('id_plato', $ids[$nombre])
                ->whereNull('pasos')
                ->update(['pasos' => $pasos]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plato') || ! Schema::hasTable('receta')) {
            return;
        }

        $ids = DB::table('plato')
            ->whereIn('nombre', array_keys($this->pasosPorPlato))
            ->pluck('id_plato');

        DB::table('receta')
            ->whereIn('id_plato', $ids->all())
            ->update(['pasos' => null]);
    }
};
