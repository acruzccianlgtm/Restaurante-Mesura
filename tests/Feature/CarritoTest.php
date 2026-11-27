<?php

namespace Tests\Feature;

use Tests\TestCase;

class CarritoTest extends TestCase
{
    public function test_invitado_no_puede_ver_el_carrito(): void
    {
        $this->get(route('carrito.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Debes iniciar sesión para acceder a esa sección.');
    }

    public function test_agregar_plato_activo_guarda_la_cantidad_en_sesion(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato(['nombre' => 'Lasaña', 'precio_final' => 10]);

        $this->actingAs($usuario)
            ->post(route('carrito.agregar', $plato), ['cantidad' => 2])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('exito', 'Lasaña se agregó a tu pedido.')
            ->assertSessionHas('carrito_'.$usuario->id, [$plato->id_plato => 2]);
    }

    public function test_agregar_plato_inactivo_se_rechaza(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato(['estado' => 'inactivo']);

        $this->actingAs($usuario)
            ->post(route('carrito.agregar', $plato), ['cantidad' => 1])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Ese platillo no está disponible.')
            ->assertSessionMissing('carrito_'.$usuario->id);
    }

    public function test_agregar_suma_cantidades_y_las_limita_a_99(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato();

        $this->actingAs($usuario)
            ->post(route('carrito.agregar', $plato), ['cantidad' => 98]);

        $this->post(route('carrito.agregar', $plato), ['cantidad' => 5])
            ->assertSessionHas('carrito_'.$usuario->id, [$plato->id_plato => 99]);
    }

    public function test_agregar_valida_que_la_cantidad_sea_positiva(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato();

        $this->actingAs($usuario)
            ->post(route('carrito.agregar', $plato), ['cantidad' => 0])
            ->assertSessionHasErrors('cantidad')
            ->assertSessionMissing('carrito_'.$usuario->id);
    }

    public function test_tipo_de_servicio_guarda_domicilio_y_calcula_envio(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato(['precio_final' => 10]);

        $this->actingAs($usuario)
            ->withSession(['carrito_'.$usuario->id => [$plato->id_plato => 2]])
            ->post(route('carrito.tipo-servicio'), ['tipo_servicio' => 'domicilio'])
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('exito', 'Se aplicó el servicio a domicilio.')
            ->assertSessionHas('tipo_servicio_'.$usuario->id, 'domicilio');

        $this->get(route('carrito.index'))
            ->assertOk()
            ->assertViewIs('carrito.index')
            ->assertViewHas('subtotal', 20.0)
            ->assertViewHas('impuesto', 3.0)
            ->assertViewHas('envio', 40.0)
            ->assertViewHas('total', 63.0)
            ->assertViewHas('tipoServicio', 'domicilio');
    }

    public function test_tipo_de_servicio_valida_opciones(): void
    {
        $usuario = $this->usuario('cliente');

        $this->actingAs($usuario)
            ->post(route('carrito.tipo-servicio'), ['tipo_servicio' => 'volador'])
            ->assertSessionHasErrors('tipo_servicio');
    }

    public function test_recoger_no_cobra_envio(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato(['precio_final' => 20]);

        $this->actingAs($usuario)
            ->withSession([
                'carrito_'.$usuario->id => [$plato->id_plato => 1],
                'tipo_servicio_'.$usuario->id => 'recoger',
            ])
            ->get(route('carrito.index'))
            ->assertViewHas('subtotal', 20.0)
            ->assertViewHas('impuesto', 3.0)
            ->assertViewHas('envio', 0.0)
            ->assertViewHas('total', 23.0);
    }

    public function test_actualizar_cantidad_cero_elimina_la_linea(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato();

        $this->actingAs($usuario)
            ->withSession(['carrito_'.$usuario->id => [$plato->id_plato => 3]])
            ->patch(route('carrito.actualizar', $plato), ['cantidad' => 0])
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('exito', 'La cantidad se actualizó correctamente.')
            ->assertSessionHas('carrito_'.$usuario->id, []);
    }

    public function test_actualizar_cantidad_valida_el_rango(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato();

        $this->actingAs($usuario)
            ->patch(route('carrito.actualizar', $plato), ['cantidad' => 100])
            ->assertSessionHasErrors('cantidad');
    }

    public function test_quitar_y_vaciar_elimina_los_datos_del_carrito(): void
    {
        $usuario = $this->usuario('cliente');
        $plato = $this->plato();

        $this->actingAs($usuario)
            ->withSession(['carrito_'.$usuario->id => [$plato->id_plato => 2]])
            ->delete(route('carrito.quitar', $plato))
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('exito', 'El platillo se quitó de tu pedido.')
            ->assertSessionHas('carrito_'.$usuario->id, []);

        $this->withSession([
            'carrito_'.$usuario->id => [$plato->id_plato => 2],
            'tipo_servicio_'.$usuario->id => 'domicilio',
        ])
            ->delete(route('carrito.vaciar'))
            ->assertRedirect(route('carrito.index'))
            ->assertSessionHas('exito', 'Tu pedido se vació correctamente.')
            ->assertSessionMissing('carrito_'.$usuario->id)
            ->assertSessionHas('tipo_servicio_'.$usuario->id, 'domicilio');
    }
}
