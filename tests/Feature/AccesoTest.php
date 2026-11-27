<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccesoTest extends TestCase
{
    public function test_pantalla_inicial_muestra_opciones_de_acceso(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('acceso.opciones')
            ->assertSee('Iniciar como cliente')
            ->assertSee('Iniciar como empleado');
    }

    public function test_formularios_de_cliente_y_empleado_estan_disponibles(): void
    {
        $this->get(route('acceso.formulario', 'cliente'))
            ->assertOk()
            ->assertViewIs('acceso.formulario')
            ->assertViewHas('tipo', 'cliente');

        $this->get(route('acceso.formulario', 'empleado'))
            ->assertOk()
            ->assertViewIs('acceso.formulario')
            ->assertViewHas('tipo', 'empleado');
    }

    public function test_tipo_de_acceso_desconocido_devuelve_404(): void
    {
        $this->get('/acceso/otro')->assertNotFound();
        $this->post('/acceso/otro', [])->assertNotFound();
    }

    public function test_login_valida_correo_y_contrasena(): void
    {
        $this->post(route('acceso.ingresar', 'cliente'), [
            'correo' => 'no-es-correo',
            'password' => '',
        ])->assertSessionHasErrors(['correo', 'password']);
    }

    public function test_login_rechaza_credenciales_incorrectas(): void
    {
        $usuario = $this->usuario('cliente', ['correo' => 'cliente@test.local']);

        $this->post(route('acceso.ingresar', 'cliente'), [
            'correo' => $usuario->correo,
            'password' => 'incorrecta',
        ])
            ->assertRedirect(route('acceso.formulario', 'cliente'))
            ->assertSessionHas('error', 'El correo o la contraseña son incorrectos.')
            ->assertSessionHasInput('correo', $usuario->correo);

        $this->assertGuest();
    }

    public function test_login_rechaza_usuario_desactivado(): void
    {
        $usuario = $this->usuario('cliente', [
            'correo' => 'inactivo@test.local',
            'estado' => 'inactivo',
        ]);

        $this->post(route('acceso.ingresar', 'cliente'), [
            'correo' => $usuario->correo,
            'password' => 'password',
        ])
            ->assertRedirect(route('acceso.formulario', 'cliente'))
            ->assertSessionHas('error', 'Tu usuario está desactivado. Solicita al administrador que lo reactive.');

        $this->assertGuest();
    }

    public function test_login_rechaza_rol_no_permitido_para_el_tipo(): void
    {
        $usuario = $this->usuario('cocinero', ['correo' => 'cocina2@test.local']);

        $this->post(route('acceso.ingresar', 'cliente'), [
            'correo' => $usuario->correo,
            'password' => 'password',
        ])
            ->assertRedirect(route('acceso.formulario', 'cliente'))
            ->assertSessionHas('error', 'Ese usuario no tiene acceso de cliente.');

        $this->assertGuest();
    }

    public function test_login_correcto_autentica_y_redirige_al_panel(): void
    {
        $usuario = $this->usuario('cliente', ['correo' => 'cliente-ok@test.local']);

        $this->post(route('acceso.ingresar', 'cliente'), [
            'correo' => $usuario->correo,
            'password' => 'password',
        ])
            ->assertRedirect(route('panel.index'))
            ->assertSessionHas('exito', 'Bienvenido, cliente.');

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_logout_cierra_sesion_y_redirige_al_inicio(): void
    {
        $usuario = $this->usuario('cliente');

        $this->actingAs($usuario)
            ->post(route('acceso.salir'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('exito', 'Cerraste sesión correctamente.');

        $this->assertGuest();
    }

    public function test_invitado_a_ruta_protegida_es_redirigido_al_inicio(): void
    {
        $this->get(route('panel.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Debes iniciar sesión para acceder a esa sección.');
    }

    public function test_usuario_sin_permiso_es_redirigido_al_inicio(): void
    {
        $usuario = $this->usuario('cliente');

        $this->actingAs($usuario)
            ->get(route('categorias.create'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');
    }

    public function test_usuario_desactivado_durante_la_sesion_cierra_sesion(): void
    {
        $usuario = $this->usuario('cliente');
        $usuario->update(['estado' => 'inactivo']);

        $this->actingAs($usuario)
            ->get(route('panel.index'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_panel_muestra_totales_para_usuario_autenticado(): void
    {
        $this->encargado();
        $this->categoria();
        $this->plato();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('panel.index'))
            ->assertOk()
            ->assertViewIs('panel.index')
            ->assertViewHas('totalPlatos', 1)
            ->assertViewHas('totalCategorias', 1)
            ->assertViewHas('totalEncargados', 2);
    }
}
