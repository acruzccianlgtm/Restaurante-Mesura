<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PersonalTest extends TestCase
{
    public function test_jefe_de_cocina_ve_listado_y_detalle_de_encargados(): void
    {
        $encargado = $this->encargado(['nombre' => 'Ana', 'apellido' => 'López']);
        $this->categoria(['nombre' => 'Postres', 'id_encargado' => $encargado->id_encargado]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('encargados.index'))
            ->assertOk()
            ->assertViewIs('encargados.index')
            ->assertViewHas('encargados')
            ->assertSee('Postres');

        $this->get(route('encargados.show', $encargado))
            ->assertOk()
            ->assertViewIs('encargados.detalles')
            ->assertViewHas('encargado', $encargado)
            ->assertSee('Postres');
    }

    public function test_crear_encargado_valido_y_campos_requeridos(): void
    {
        $this->actingAs($this->usuario('encargado'))
            ->post(route('encargados.store'), [
                'nombre' => 'Carlos',
                'apellido' => 'Ruiz',
                'cargo' => 'Encargado de categoria',
                'telefono' => '600111222',
            ])
            ->assertRedirect(route('encargados.index'))
            ->assertSessionHas('exito', 'El encargado se registró correctamente.');

        $this->assertDatabaseHas('encargado_plato', ['nombre' => 'Carlos', 'apellido' => 'Ruiz']);

        $this->post(route('encargados.store'), [
            'nombre' => '',
            'apellido' => '',
        ])->assertSessionHasErrors(['nombre', 'apellido']);
    }

    public function test_editar_encargado_inactivo_se_rechaza(): void
    {
        $encargado = $this->encargado(['estado' => 'inactivo']);

        $this->actingAs($this->usuario('encargado'))
            ->get(route('encargados.edit', $encargado))
            ->assertRedirect(route('encargados.index'))
            ->assertSessionHas('error', 'Reactiva el encargado antes de editarlo.');
    }

    public function test_actualizar_y_cambiar_estado_de_encargado(): void
    {
        $encargado = $this->encargado(['nombre' => 'Viejo']);

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('encargados.update', $encargado), [
                'nombre' => 'Nuevo',
                'apellido' => 'Apellido',
            ])
            ->assertRedirect(route('encargados.index'))
            ->assertSessionHas('exito', 'El encargado se actualizó correctamente.');

        $this->assertDatabaseHas('encargado_plato', ['id_encargado' => $encargado->id_encargado, 'nombre' => 'Nuevo']);

        $this->patch(route('encargados.estado', $encargado))
            ->assertSessionHas('exito', 'Encargado desactivado.');

        $this->assertSame('inactivo', $encargado->fresh()->estado);

        $this->patch(route('encargados.estado', $encargado))
            ->assertSessionHas('exito', 'Encargado reactivado.');

        $this->assertSame('activo', $encargado->fresh()->estado);
    }

    public function test_eliminar_encargado_con_categorias_se_bloquea_y_sin_ellas_funciona(): void
    {
        $encargado = $this->encargado(['nombre' => 'Con categoría']);
        $this->categoria(['nombre' => 'Bebidas', 'id_encargado' => $encargado->id_encargado]);

        $this->actingAs($this->usuario('encargado'))
            ->get(route('encargados.eliminar', $encargado))
            ->assertOk()
            ->assertViewIs('encargados.eliminar')
            ->assertSee('Bebidas');

        $this->delete(route('encargados.destroy', $encargado))
            ->assertRedirect(route('encargados.index'))
            ->assertSessionHas('error', 'No se puede eliminar el encargado porque tiene categorías asignadas.');

        $this->assertDatabaseHas('encargado_plato', ['id_encargado' => $encargado->id_encargado]);

        $categoria = $encargado->categorias()->firstOrFail();
        $categoria->delete();

        $this->delete(route('encargados.destroy', $encargado))
            ->assertSessionHas('exito', 'El encargado se eliminó correctamente.');

        $this->assertDatabaseMissing('encargado_plato', ['id_encargado' => $encargado->id_encargado]);
    }

    public function test_encargado_ve_listado_y_detalle_de_usuarios(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'mesero@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertViewIs('usuarios.index')
            ->assertViewHas('usuarios');

        $this->get(route('usuarios.show', $usuario))
            ->assertOk()
            ->assertViewIs('usuarios.detalles')
            ->assertViewHas('usuario', $usuario);
    }

    public function test_formulario_de_usuario_expone_roles_y_categorias(): void
    {
        $categoria = $this->categoria();

        $this->actingAs($this->usuario('encargado'))
            ->get(route('usuarios.create'))
            ->assertOk()
            ->assertViewIs('usuarios.create')
            ->assertViewHas('roles', ['mesero', 'cajero', 'cocinero', 'encargado de categoria'])
            ->assertSee($categoria->nombre);
    }

    public function test_crear_usuario_mesero_fuerza_categoria_nula_y_sincroniza_rol(): void
    {
        $this->actingAs($this->usuario('encargado'))
            ->post(route('usuarios.store'), [
                'correo' => 'nuevo-mesero@test.local',
                'password' => 'password123',
                'rol' => 'mesero',
                'id_categoria' => $this->categoria()->id_categoria,
                ...$this->datosPersonales(),
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito', 'El usuario se creó correctamente.');

        $usuario = User::query()->where('correo', 'nuevo-mesero@test.local')->firstOrFail();

        $this->assertNull($usuario->id_categoria);
        $this->assertTrue($usuario->hasRole('mesero'));
        $this->assertTrue($usuario->estaActivo());
    }

    public function test_crear_encargado_de_categoria_requiere_categoria(): void
    {
        $categoria = $this->categoria();

        $this->actingAs($this->usuario('encargado'))
            ->post(route('usuarios.store'), [
                'correo' => 'encargado-cat@test.local',
                'password' => 'password123',
                'rol' => 'encargado de categoria',
                ...$this->datosPersonales(),
            ])
            ->assertSessionHasErrors('id_categoria');

        $this->post(route('usuarios.store'), [
            'correo' => 'encargado-cat@test.local',
            'password' => 'password123',
            'rol' => 'encargado de categoria',
            'id_categoria' => $categoria->id_categoria,
            ...$this->datosPersonales(),
        ])->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('usuarios', [
            'correo' => 'encargado-cat@test.local',
            'id_categoria' => $categoria->id_categoria,
        ]);
    }

    public function test_crear_usuario_valida_correo_contrasena_y_rol(): void
    {
        $existente = $this->usuario('mesero', ['correo' => 'duplicado@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->post(route('usuarios.store'), [
                'correo' => $existente->correo,
                'password' => 'corta',
                'rol' => 'cliente',
                ...$this->datosPersonales(),
            ])
            ->assertSessionHasErrors(['correo', 'password', 'rol']);
    }

    public function test_editar_usuario_inactivo_se_rechaza(): void
    {
        $usuario = $this->usuario('mesero', ['estado' => 'inactivo']);

        $this->actingAs($this->usuario('encargado'))
            ->get(route('usuarios.edit', $usuario))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error', 'Reactiva el usuario antes de editarlo.');
    }

    public function test_actualizar_usuario_cambia_rol_y_permisos_spatie(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'cambiar-rol@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('usuarios.update', $usuario), [
                'correo' => $usuario->correo,
                'rol' => 'cajero',
                ...$this->datosPersonales(),
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito', 'El usuario se actualizó correctamente.');

        $usuario->refresh();

        $this->assertSame('cajero', $usuario->rol);
        $this->assertTrue($usuario->hasRole('cajero'));
        $this->assertFalse($usuario->hasRole('mesero'));
    }

    public function test_usuario_no_puede_desactivarse_a_si_mismo(): void
    {
        $usuario = $this->usuario('encargado');

        $this->actingAs($usuario)
            ->patch(route('usuarios.estado', $usuario))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error', 'No puedes desactivar tu propio usuario.');

        $this->assertSame('activo', $usuario->fresh()->estado);
    }

    public function test_cambiar_estado_de_otro_usuario_alterna_estado(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'toggle@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('usuarios.estado', $usuario))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito', 'Usuario desactivado.');

        $this->assertSame('inactivo', $usuario->fresh()->estado);

        $this->patch(route('usuarios.estado', $usuario))
            ->assertSessionHas('exito', 'Usuario reactivado.');

        $this->assertSame('activo', $usuario->fresh()->estado);
    }

    public function test_datos_personales_se_guardan_al_crear_y_actualizar_usuario(): void
    {
        $this->actingAs($this->usuario('encargado'))
            ->post(route('usuarios.store'), [
                'correo' => 'datos@test.local',
                'password' => 'password123',
                'rol' => 'mesero',
                ...$this->datosPersonales(['nombre' => 'Ana', 'apellido' => 'Pérez']),
            ])
            ->assertRedirect(route('usuarios.index'));

        $usuario = User::query()->where('correo', 'datos@test.local')->firstOrFail();

        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'edad' => 30,
            'telefono' => '600111222',
            'ciudad' => 'Tegucigalpa',
        ]);

        $this->patch(route('usuarios.update', $usuario), [
            'correo' => $usuario->correo,
            'rol' => 'mesero',
            ...$this->datosPersonales([
                'nombre' => 'Ana María',
                'apellido' => 'López',
                'edad' => 31,
                'ciudad' => 'Comayagua',
            ]),
        ])->assertSessionHas('exito', 'El usuario se actualizó correctamente.');

        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nombre' => 'Ana María',
            'apellido' => 'López',
            'edad' => 31,
            'ciudad' => 'Comayagua',
        ]);
    }

    public function test_formularios_de_crear_y_editar_usuario_usan_los_mismos_campos(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'formulario@test.local']);
        $campos = ['nombre', 'apellido', 'edad', 'telefono', 'ciudad', 'correo', 'password', 'rol', 'id_categoria'];

        $crear = $this->actingAs($this->usuario('encargado'))
            ->get(route('usuarios.create'))
            ->assertOk();

        foreach ($campos as $campo) {
            $crear->assertSee('name="'.$campo.'"', false);
        }

        $editar = $this->get(route('usuarios.edit', $usuario))
            ->assertOk();

        foreach ($campos as $campo) {
            $editar->assertSee('name="'.$campo.'"', false);
        }
    }

    public function test_editar_usuario_no_ofrece_rol_encargado(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'sin-encargado@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->get(route('usuarios.edit', $usuario))
            ->assertOk()
            ->assertViewHas('roles', fn (array $roles): bool => ! in_array('encargado', $roles, true));
    }

    public function test_no_se_puede_actualizar_usuario_a_rol_encargado(): void
    {
        $usuario = $this->usuario('mesero', ['correo' => 'rol-bloqueado@test.local']);

        $this->actingAs($this->usuario('encargado'))
            ->patch(route('usuarios.update', $usuario), [
                'correo' => $usuario->correo,
                'rol' => 'encargado',
                ...$this->datosPersonales(),
            ])
            ->assertSessionHasErrors('rol');
    }

    /**
     * @param  array<string, mixed>  $atributos
     * @return array<string, mixed>
     */
    private function datosPersonales(array $atributos = []): array
    {
        return array_merge([
            'nombre' => 'Carlos',
            'apellido' => 'Ruiz',
            'edad' => 30,
            'telefono' => '600111222',
            'ciudad' => 'Tegucigalpa',
        ], $atributos);
    }
}
