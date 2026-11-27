<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

class MesasYReservasTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_jefe_de_cocina_ve_listado_y_vistas_de_mesas(): void
    {
        $mesa = $this->mesa();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('mesas.index'))
            ->assertOk()
            ->assertViewIs('mesas.index')
            ->assertViewHas('mesas')
            ->assertViewHas('reservas');

        $this->get(route('mesas.create'))->assertOk()->assertViewIs('mesas.create');
        $this->get(route('mesas.edit', $mesa))->assertOk()->assertViewIs('mesas.edit');
        $this->get(route('mesas.eliminar', $mesa))->assertOk()->assertViewIs('mesas.eliminar');
    }

    public function test_crear_mesa_valida_capacidad_y_guarda_estado_disponible(): void
    {
        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('mesas.store'), ['capacidad' => 0])
            ->assertSessionHasErrors('capacidad');

        $this->post(route('mesas.store'), ['capacidad' => 31])
            ->assertSessionHasErrors('capacidad');

        $this->post(route('mesas.store'), ['capacidad' => 6])
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La mesa se creó correctamente.');

        $this->assertDatabaseHas('mesa', ['capacidad' => 6, 'estado' => 'disponible']);
    }

    public function test_actualizar_capacidad_de_mesa(): void
    {
        $mesa = $this->mesa(['capacidad' => 2]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->patch(route('mesas.update', $mesa), ['capacidad' => 8])
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La capacidad de la mesa se actualizó correctamente.');

        $this->assertSame(8, $mesa->fresh()->capacidad);
    }

    public function test_eliminar_mesa_cascadea_sus_reservas(): void
    {
        $mesa = $this->mesa();
        $reserva = $this->reserva(['id_mesa' => $mesa->id_mesa]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->delete(route('mesas.destroy', $mesa))
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La mesa se eliminó correctamente.');

        $this->assertDatabaseMissing('mesa', ['id_mesa' => $mesa->id_mesa]);
        $this->assertDatabaseMissing('reserva', ['id_reserva' => $reserva->id_reserva]);
    }

    public function test_marca_mesa_como_reservada_y_crea_reserva_sintetica(): void
    {
        Carbon::setTestNow('2026-11-24 20:15:00');
        $mesa = $this->mesa(['capacidad' => 5]);

        $this->actingAs($this->usuario('mesero'))
            ->patch(route('mesas.estado', $mesa), ['estado' => 'reservada'])
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'El estado de la mesa se actualizó correctamente.');

        $this->assertSame('reservada', $mesa->fresh()->estado);
        $this->assertDatabaseHas('reserva', [
            'id_mesa' => $mesa->id_mesa,
            'nombre_cliente' => 'Reserva de mesa',
            'origen' => 'mesa',
            'estado' => 'activa',
            'cantidad_personas' => 5,
            'fecha_reserva' => '2026-11-24',
            'hora_reserva' => '20:15',
        ]);
    }

    public function test_pasar_a_disponible_cancela_solo_reservas_desde_mesa(): void
    {
        $mesa = $this->mesa();
        $manual = $this->reserva(['id_mesa' => $mesa->id_mesa, 'origen' => 'manual']);
        $desdeMesa = $this->reserva(['id_mesa' => $mesa->id_mesa, 'origen' => 'mesa']);

        $this->actingAs($this->usuario('mesero'))
            ->patch(route('mesas.estado', $mesa), ['estado' => 'disponible']);

        $this->assertSame('activa', $manual->fresh()->estado);
        $this->assertSame('cancelada', $desdeMesa->fresh()->estado);
    }

    public function test_estado_de_mesa_valida_valores_permitidos(): void
    {
        $mesa = $this->mesa();

        $this->actingAs($this->usuario('mesero'))
            ->patch(route('mesas.estado', $mesa), ['estado' => 'invisible'])
            ->assertSessionHasErrors('estado');
    }

    public function test_mesero_sin_permiso_no_puede_gestionar_reservas(): void
    {
        $this->actingAs($this->usuario('mesero'))
            ->get(route('reservas.create'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');
    }

    public function test_jefe_de_cocina_crea_una_reserva_valida(): void
    {
        $mesa = $this->mesa(['capacidad' => 4]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('reservas.create'))
            ->assertOk()
            ->assertViewIs('reservas.create')
            ->assertViewHas('mesas');

        $this->post(route('reservas.store'), [
            'id_mesa' => $mesa->id_mesa,
            'nombre_cliente' => 'Cliente Real',
            'telefono' => '600222333',
            'fecha_reserva' => '2026-11-25',
            'hora_reserva' => '21:30',
            'cantidad_personas' => 3,
        ])
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La reserva se creó correctamente.');

        $this->assertDatabaseHas('reserva', [
            'id_mesa' => $mesa->id_mesa,
            'nombre_cliente' => 'Cliente Real',
            'estado' => 'activa',
            'origen' => 'manual',
        ]);
    }

    public function test_reserva_no_puede_superar_capacidad_de_mesa(): void
    {
        $mesa = $this->mesa(['capacidad' => 2]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('reservas.store'), [
                'id_mesa' => $mesa->id_mesa,
                'nombre_cliente' => 'Cliente Excedido',
                'fecha_reserva' => '2026-11-25',
                'hora_reserva' => '21:30',
                'cantidad_personas' => 5,
            ])
            ->assertSessionHas('error', 'La cantidad de personas no puede superar la capacidad de la mesa.');

        $this->assertDatabaseMissing('reserva', ['nombre_cliente' => 'Cliente Excedido']);
    }

    public function test_reserva_valida_campos(): void
    {
        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('reservas.store'), [
                'id_mesa' => 99999,
                'nombre_cliente' => '',
                'fecha_reserva' => 'no-fecha',
                'hora_reserva' => '25:99',
                'cantidad_personas' => 0,
            ])
            ->assertSessionHasErrors(['id_mesa', 'nombre_cliente', 'fecha_reserva', 'hora_reserva', 'cantidad_personas']);
    }

    public function test_actualizar_y_cancelar_reserva(): void
    {
        $mesa = $this->mesa(['capacidad' => 6]);
        $reserva = $this->reserva(['id_mesa' => $mesa->id_mesa]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('reservas.edit', $reserva))
            ->assertOk()
            ->assertViewIs('reservas.edit');

        $this->patch(route('reservas.update', $reserva), [
            'id_mesa' => $mesa->id_mesa,
            'nombre_cliente' => 'Cliente Actualizado',
            'fecha_reserva' => '2026-11-26',
            'hora_reserva' => '19:00',
            'cantidad_personas' => 4,
        ])
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La reserva se actualizó correctamente.');

        $this->assertSame('Cliente Actualizado', $reserva->fresh()->nombre_cliente);

        $this->patch(route('reservas.cancelar', $reserva))
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('exito', 'La reserva se canceló correctamente.');

        $this->assertSame('cancelada', $reserva->fresh()->estado);
    }

    public function test_reserva_cancelada_no_puede_editarse_ni_cancelarse(): void
    {
        $reserva = $this->reserva(['estado' => 'cancelada']);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('reservas.edit', $reserva))
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('error', 'La reserva está cancelada y no se puede editar.');

        $this->patch(route('reservas.cancelar', $reserva))
            ->assertRedirect(route('mesas.index'))
            ->assertSessionHas('error', 'La reserva está cancelada y no se puede editar.');
    }
}
