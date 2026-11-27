<?php

namespace Tests\Feature;

use Tests\TestCase;

class InventarioTest extends TestCase
{
    public function test_cocinero_ve_inventario_y_puede_actualizar_cantidad(): void
    {
        $ingrediente = $this->ingrediente(['cantidad_actual_almacen' => 10]);

        $this->actingAs($this->usuario('cocinero'))
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertViewIs('inventario.index')
            ->assertViewHas('ingredientes');

        $this->patch(route('inventario.cantidad', $ingrediente), ['cantidad_actual_almacen' => 4])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('exito', 'Cantidad del ingrediente actualizada correctamente.');

        $this->assertSame('4.00', $ingrediente->fresh()->cantidad_actual_almacen);
    }

    public function test_cocinero_no_puede_crear_ingredientes(): void
    {
        $this->actingAs($this->usuario('cocinero'))
            ->post(route('inventario.store'), [])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');
    }

    public function test_jefe_de_cocina_crea_ingrediente_y_valida_duplicados(): void
    {
        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('inventario.store'), [
                'nombre' => 'Harina',
                'cantidad_actual_almacen' => 20,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 5,
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('exito', 'Ingrediente agregado al inventario.');

        $this->assertDatabaseHas('ingrediente', [
            'nombre' => 'Harina',
            'estado' => 'activo',
        ]);

        $this->post(route('inventario.store'), [
            'nombre' => 'Harina',
            'cantidad_actual_almacen' => 1,
            'unidad_medida_almacen' => 'kg',
            'cantidad_minima' => 1,
        ])->assertSessionHasErrors('nombre');

        $this->post(route('inventario.store'), [
            'nombre' => 'Negativo',
            'cantidad_actual_almacen' => -1,
            'unidad_medida_almacen' => 'kg',
            'cantidad_minima' => -2,
        ])->assertSessionHasErrors(['cantidad_actual_almacen', 'cantidad_minima']);
    }

    public function test_actualizar_ingrediente_ignora_su_propio_nombre(): void
    {
        $ingrediente = $this->ingrediente(['nombre' => 'Azúcar']);

        $this->actingAs($this->usuario('encargado'))
            ->put(route('inventario.update', $ingrediente), [
                'nombre' => 'Azúcar',
                'cantidad_actual_almacen' => 30,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 10,
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('exito', 'Ingrediente actualizado correctamente.');

        $this->assertSame('30.00', $ingrediente->fresh()->cantidad_actual_almacen);
    }

    public function test_editar_ingrediente_inactivo_se_rechaza(): void
    {
        $ingrediente = $this->ingrediente(['estado' => 'inactivo']);

        $this->actingAs($this->usuario('encargado'))
            ->put(route('inventario.update', $ingrediente), [
                'nombre' => 'Inactivo',
                'cantidad_actual_almacen' => 1,
                'unidad_medida_almacen' => 'kg',
                'cantidad_minima' => 1,
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('error', 'Reactiva el ingrediente antes de editarlo.');
    }

    public function test_actualizar_cantidad_de_ingrediente_inactivo_se_rechaza(): void
    {
        $ingrediente = $this->ingrediente(['estado' => 'inactivo']);

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('inventario.cantidad', $ingrediente), ['cantidad_actual_almacen' => 5])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('error', 'Reactiva el ingrediente antes de editar su cantidad.');
    }

    public function test_actualizar_cantidad_valida_valores(): void
    {
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('inventario.cantidad', $ingrediente), ['cantidad_actual_almacen' => 'no-numero'])
            ->assertSessionHasErrors('cantidad_actual_almacen');
    }

    public function test_cambiar_estado_de_ingrediente_alterna_estado(): void
    {
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('inventario.estado', $ingrediente))
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('exito', 'Ingrediente desactivado.');

        $this->assertSame('inactivo', $ingrediente->fresh()->estado);

        $this->patch(route('inventario.estado', $ingrediente))
            ->assertSessionHas('exito', 'Ingrediente reactivado.');

        $this->assertSame('activo', $ingrediente->fresh()->estado);
    }

    public function test_jefe_de_cocina_no_tiene_permiso_para_actualizar_cantidad(): void
    {
        $ingrediente = $this->ingrediente();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->patch(route('inventario.cantidad', $ingrediente), ['cantidad_actual_almacen' => 1])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');
    }
}
