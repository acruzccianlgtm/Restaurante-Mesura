<?php

namespace Database\Seeders;

use App\Models\Ingrediente;
use Illuminate\Database\Seeder;

class IngredienteSeeder extends Seeder
{
    public function run(): void
    {
        $ejemplos = [
            [
                'nombre' => 'Sal marina',
                'cantidad_actual_almacen' => 1.5,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 2,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Pollo fresco',
                'cantidad_actual_almacen' => 0,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 4,
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Azúcar',
                'cantidad_actual_almacen' => 10,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 3,
                'estado' => 'inactivo',
            ],
        ];

        foreach ($ejemplos as $ejemplo) {
            Ingrediente::query()->firstOrCreate(
                ['nombre' => $ejemplo['nombre']],
                $ejemplo,
            );
        }
    }
}
