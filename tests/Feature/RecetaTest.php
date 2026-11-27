<?php

namespace Tests\Feature;

use App\Models\Plato;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecetaTest extends TestCase
{
    public function test_cocinero_ve_los_pasos_de_la_receta_en_el_detalle(): void
    {
        $plato = $this->platoConReceta();

        $this->actingAs($this->usuario('cocinero'))
            ->get(route('platos.show', $plato))
            ->assertOk()
            ->assertSee('Receta')
            ->assertSee('Primer paso.')
            ->assertSee('Segundo paso.')
            ->assertDontSee('Editar receta');
    }

    public function test_jefe_de_cocina_y_encargado_de_categoria_pueden_ver_y_editar_la_receta(): void
    {
        $plato = $this->platoConReceta();
        $encargado = $this->usuario('encargado de categoria', ['id_categoria' => $plato->id_categoria]);

        foreach ([$this->usuario('jefe de cocina'), $encargado] as $usuario) {
            $this->actingAs($usuario)
                ->get(route('platos.show', $plato))
                ->assertOk()
                ->assertSee('Primer paso.')
                ->assertSee('Editar receta');

            $this->get(route('platos.receta.edit', $plato))
                ->assertOk()
                ->assertViewIs('platos.receta')
                ->assertSee('Primer paso.');
        }
    }

    public function test_otros_roles_no_ven_la_receta(): void
    {
        $plato = $this->platoConReceta();

        foreach (['cliente', 'mesero', 'cajero', 'encargado'] as $rol) {
            $this->actingAs($this->usuario($rol))
                ->get(route('platos.show', $plato))
                ->assertOk()
                ->assertDontSee('Primer paso.');
        }
    }

    public function test_cocinero_no_puede_editar_la_receta(): void
    {
        $plato = $this->platoConReceta();
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('cocinero'))
            ->get(route('platos.receta.edit', $plato))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');

        $this->put(route('platos.receta.update', $plato), [
            'pasos' => 'Paso modificado.',
            'receta' => $this->filaReceta($ingrediente->id_ingrediente),
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('receta', [
            'id_plato' => $plato->id_plato,
            'pasos' => "Primer paso.\nSegundo paso.",
        ]);
    }

    public function test_jefe_de_cocina_actualiza_los_pasos_y_los_ingredientes(): void
    {
        $plato = $this->platoConReceta('Paso original.');
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->put(route('platos.receta.update', $plato), [
                'pasos' => "Nuevo paso uno.\nNuevo paso dos.",
                'receta' => $this->filaReceta($ingrediente->id_ingrediente, '2.500', 'lb'),
            ])
            ->assertRedirect(route('platos.show', $plato))
            ->assertSessionHas('exito', 'La receta se actualizó correctamente.');

        $this->assertDatabaseHas('receta', [
            'id_plato' => $plato->id_plato,
            'id_ingrediente' => $ingrediente->id_ingrediente,
            'unidad_medida' => 'lb',
            'pasos' => "Nuevo paso uno.\nNuevo paso dos.",
        ]);
        $this->assertDatabaseCount('receta', 1);
    }

    public function test_encargado_de_categoria_no_puede_editar_receta_de_otra_categoria(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $plato = $this->plato(['id_categoria' => $ajena->id_categoria]);
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('encargado de categoria', ['id_categoria' => $propia->id_categoria]))
            ->put(route('platos.receta.update', $plato), [
                'pasos' => 'Paso ajeno.',
                'receta' => $this->filaReceta($ingrediente->id_ingrediente),
            ])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Solo puedes gestionar platillos de la categoría asignada.');

        $this->assertDatabaseMissing('receta', [
            'id_plato' => $plato->id_plato,
            'pasos' => 'Paso ajeno.',
        ]);
    }

    public function test_editar_receta_de_plato_inactivo_se_rechaza(): void
    {
        $plato = $this->plato(['estado' => 'inactivo']);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('platos.receta.edit', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Reactiva el platillo antes de editar su receta.');
    }

    private function platoConReceta(string $pasos = "Primer paso.\nSegundo paso."): Plato
    {
        $plato = $this->plato();
        $ingrediente = $this->ingrediente();

        DB::table('receta')->insert([
            'id_plato' => $plato->id_plato,
            'id_ingrediente' => $ingrediente->id_ingrediente,
            'cantidad_requerida' => 1,
            'unidad_medida' => 'kg',
            'pasos' => $pasos,
        ]);

        return $plato;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filaReceta(int $idIngrediente, string $cantidad = '1.000', string $unidad = 'kg'): array
    {
        return [
            [
                'id_ingrediente' => $idIngrediente,
                'cantidad_requerida' => $cantidad,
                'unidad_medida' => $unidad,
            ],
        ];
    }
}
