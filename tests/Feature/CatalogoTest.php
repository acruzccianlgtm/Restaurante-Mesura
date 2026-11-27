<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Plato;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    public function test_cliente_puede_ver_la_carta_y_el_detalle_de_un_plato(): void
    {
        $plato = $this->plato(['nombre' => 'Enchiladas']);

        $this->actingAs($this->usuario('cliente'))
            ->get(route('platos.index'))
            ->assertOk()
            ->assertViewIs('platos.index')
            ->assertViewHas('platos');

        $this->get(route('platos.show', $plato))
            ->assertOk()
            ->assertViewIs('platos.detalles')
            ->assertViewHas('plato', $plato);
    }

    public function test_plato_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->usuario('jefe de cocina'))
            ->get('/platos/99999')
            ->assertNotFound();
    }

    public function test_jefe_de_cocina_crea_un_plato_valido(): void
    {
        $categoria = $this->categoria();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('platos.store'), [
                'nombre' => 'Nuevo plato',
                'descripcion' => 'Descripción',
                'nivel_dificultad' => 3,
                'precio_final' => 12.50,
                'id_categoria' => $categoria->id_categoria,
                ...$this->datosReceta(),
            ])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('exito', 'El platillo se agregó correctamente a la carta.');

        $this->assertDatabaseHas('plato', [
            'nombre' => 'Nuevo plato',
            'id_categoria' => $categoria->id_categoria,
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('receta', [
            'id_plato' => Plato::query()->where('nombre', 'Nuevo plato')->value('id_plato'),
            'pasos' => "Paso uno.\nPaso dos.",
        ]);
    }

    public function test_crear_plato_valida_nombre_unico_y_datos(): void
    {
        $plato = $this->plato(['nombre' => 'Repetido']);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('platos.store'), [
                'nombre' => 'Repetido',
                'nivel_dificultad' => 6,
                'precio_final' => -1,
                'id_categoria' => 99999,
                ...$this->datosReceta(),
            ])
            ->assertSessionHasErrors(['nombre', 'nivel_dificultad', 'precio_final', 'id_categoria']);

        $this->assertDatabaseCount('plato', 1);
        $this->assertSame($plato->id_plato, Plato::query()->value('id_plato'));
    }

    public function test_editar_plato_inactivo_se_rechaza(): void
    {
        $plato = $this->plato(['estado' => 'inactivo']);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('platos.edit', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Reactiva el platillo antes de editarlo.');
    }

    public function test_actualizar_plato_cambia_sus_datos(): void
    {
        $plato = $this->plato(['nombre' => 'Viejo nombre']);
        $categoria = $this->categoria();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->patch(route('platos.update', $plato), [
                'nombre' => 'Nuevo nombre',
                'descripcion' => 'Actualizado',
                'nivel_dificultad' => 2,
                'precio_final' => 15.75,
                'id_categoria' => $categoria->id_categoria,
            ])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('exito', 'El platillo se actualizó correctamente.');

        $this->assertDatabaseHas('plato', ['id_plato' => $plato->id_plato, 'nombre' => 'Nuevo nombre']);
        $this->assertDatabaseMissing('plato', ['nombre' => 'Viejo nombre']);
    }

    public function test_cambiar_estado_de_plato_alterna_y_muestra_mensaje(): void
    {
        $plato = $this->plato();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->patch(route('platos.estado', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('exito', 'Platillo desactivado.');

        $this->assertSame('inactivo', $plato->fresh()->estado);

        $this->patch(route('platos.estado', $plato))
            ->assertSessionHas('exito', 'Platillo reactivado.');

        $this->assertSame('activo', $plato->fresh()->estado);
    }

    public function test_encargado_de_categoria_solo_ve_su_categoria_en_el_formulario(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $usuario = $this->usuario('encargado de categoria', ['id_categoria' => $propia->id_categoria]);

        $this->actingAs($usuario)
            ->get(route('platos.create'))
            ->assertOk()
            ->assertViewHas('categorias')
            ->assertSee('Propia')
            ->assertDontSee('Ajena');
    }

    public function test_encargado_de_categoria_no_puede_crear_plato_en_otra_categoria(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $usuario = $this->usuario('encargado de categoria', ['id_categoria' => $propia->id_categoria]);

        $this->actingAs($usuario)
            ->post(route('platos.store'), [
                'nombre' => 'Plato ajeno',
                'nivel_dificultad' => 1,
                'precio_final' => 10,
                'id_categoria' => $ajena->id_categoria,
                ...$this->datosReceta(),
            ])
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Solo puedes gestionar platillos de la categoría asignada.');

        $this->assertDatabaseMissing('plato', ['nombre' => 'Plato ajeno']);
    }

    public function test_encargado_de_categoria_no_puede_editar_plato_ajeno(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $plato = $this->plato(['id_categoria' => $ajena->id_categoria]);
        $usuario = $this->usuario('encargado de categoria', ['id_categoria' => $propia->id_categoria]);

        $this->actingAs($usuario)
            ->get(route('platos.edit', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Solo puedes gestionar platillos de la categoría asignada.');
    }

    public function test_subir_foto_guarda_archivo_y_ruta_relativa(): void
    {
        $categoria = $this->categoria();
        $archivo = UploadedFile::fake()->image('foto.jpg');

        $this->actingAs($this->usuario('jefe de cocina'))
            ->post(route('platos.store'), [
                'nombre' => 'Plato con foto',
                'nivel_dificultad' => 1,
                'precio_final' => 10,
                'id_categoria' => $categoria->id_categoria,
                'foto' => $archivo,
                ...$this->datosReceta(),
            ])
            ->assertRedirect(route('platos.index'));

        $plato = Plato::query()->where('nombre', 'Plato con foto')->firstOrFail();

        try {
            $this->assertStringStartsWith('uploads/platos/', $plato->foto);
            $this->assertFileExists(public_path($plato->foto));
        } finally {
            if ($plato->foto && file_exists(public_path($plato->foto))) {
                unlink(public_path($plato->foto));
            }
        }
    }

    public function test_eliminar_plato_con_recetas_se_bloquea(): void
    {
        $plato = $this->plato();
        $ingrediente = $this->ingrediente();

        DB::table('receta')->insert([
            'id_plato' => $plato->id_plato,
            'id_ingrediente' => $ingrediente->id_ingrediente,
            'cantidad_requerida' => 1,
            'unidad_medida' => 'kg',
        ]);

        $this->actingAs($this->usuario('jefe de cocina'))
            ->delete(route('platos.destroy', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'No se puede eliminar el platillo porque tiene recetas o pedidos relacionados.');

        $this->assertDatabaseHas('plato', ['id_plato' => $plato->id_plato]);
    }

    public function test_eliminar_plato_sin_relaciones_funciona(): void
    {
        $plato = $this->plato();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->get(route('platos.eliminar', $plato))
            ->assertOk()
            ->assertViewIs('platos.eliminar');

        $this->delete(route('platos.destroy', $plato))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('exito', 'El platillo se eliminó de la carta.');

        $this->assertDatabaseMissing('plato', ['id_plato' => $plato->id_plato]);
    }

    public function test_cliente_puede_ver_categorias(): void
    {
        $categoria = $this->categoria(['nombre' => 'Postres']);

        $this->actingAs($this->usuario('cliente'))
            ->get(route('categorias.index'))
            ->assertOk()
            ->assertViewIs('categorias.index')
            ->assertViewHas('categorias');

        $this->get(route('categorias.show', $categoria))
            ->assertOk()
            ->assertViewIs('categorias.detalles')
            ->assertViewHas('categoria', $categoria);
    }

    public function test_jefe_de_cocina_crea_y_valida_categorias(): void
    {
        $encargado = $this->encargado();
        $usuario = $this->usuario('jefe de cocina');

        $this->actingAs($usuario)
            ->post(route('categorias.store'), [
                'nombre' => 'Bebidas',
                'descripcion' => 'Frías y calientes',
                'id_encargado' => $encargado->id_encargado,
            ])
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'La categoría se registró correctamente.');

        $this->assertDatabaseHas('categoria', ['nombre' => 'Bebidas']);

        $this->post(route('categorias.store'), [
            'nombre' => 'Bebidas',
            'id_encargado' => $encargado->id_encargado,
        ])->assertSessionHasErrors('nombre');

        $this->post(route('categorias.store'), [
            'nombre' => 'Sin encargado',
            'id_encargado' => 99999,
        ])->assertSessionHasErrors('id_encargado');
    }

    public function test_editar_categoria_inactiva_se_rechaza_y_estado_se_alterna(): void
    {
        $categoria = $this->categoria(['estado' => 'inactivo']);
        $usuario = $this->usuario('jefe de cocina');

        $this->actingAs($usuario)
            ->get(route('categorias.edit', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('error', 'Reactiva la categoría antes de editarla.');

        $this->patch(route('categorias.estado', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'Categoría reactivada.');

        $this->assertSame('activo', $categoria->fresh()->estado);

        $this->patch(route('categorias.estado', $categoria))
            ->assertSessionHas('exito', 'Categoría desactivada.');

        $this->assertSame('inactivo', $categoria->fresh()->estado);
    }

    public function test_actualizar_categoria_valida_unico_ignorando_su_propio_id(): void
    {
        $categoria = $this->categoria(['nombre' => 'Original']);
        $encargado = $this->encargado();

        $this->actingAs($this->usuario('jefe de cocina'))
            ->patch(route('categorias.update', $categoria), [
                'nombre' => 'Original',
                'id_encargado' => $encargado->id_encargado,
            ])
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'La categoría se actualizó correctamente.');
    }

    public function test_eliminar_categoria_con_platos_se_bloquea_y_sin_ellos_funciona(): void
    {
        $categoria = $this->categoria(['nombre' => 'Con platos']);
        $this->plato(['id_categoria' => $categoria->id_categoria]);
        $usuario = $this->usuario('jefe de cocina');

        $this->actingAs($usuario)
            ->get(route('categorias.eliminar', $categoria))
            ->assertOk()
            ->assertViewIs('categorias.eliminar');

        $this->delete(route('categorias.destroy', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('error', 'No se puede eliminar la categoría porque tiene platillos registrados.');

        $this->assertDatabaseHas('categoria', ['id_categoria' => $categoria->id_categoria]);

        $vacia = Categoria::query()->where('nombre', 'Con platos')->firstOrFail();
        $plato = Plato::query()->where('id_categoria', $vacia->id_categoria)->firstOrFail();
        $plato->delete();

        $this->delete(route('categorias.destroy', $vacia))
            ->assertSessionHas('exito', 'La categoría se eliminó correctamente.');

        $this->assertDatabaseMissing('categoria', ['id_categoria' => $vacia->id_categoria]);
    }

    public function test_encargado_de_categoria_ve_carta_e_inventario_y_solo_edita_su_categoria(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $platoPropio = $this->plato(['nombre' => 'Plato propio', 'id_categoria' => $propia->id_categoria]);
        $platoAjeno = $this->plato(['nombre' => 'Plato ajeno', 'id_categoria' => $ajena->id_categoria]);
        $ingrediente = $this->ingrediente(['nombre' => 'Harina']);
        $usuario = $this->usuario('encargado de categoria', ['id_categoria' => $propia->id_categoria]);

        $this->actingAs($usuario)
            ->get(route('platos.index'))
            ->assertOk()
            ->assertSee('Plato propio')
            ->assertSee('Plato ajeno')
            ->assertSee(route('platos.edit', $platoPropio))
            ->assertDontSee(route('platos.edit', $platoAjeno));

        $this->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('Harina');

        $this->patch(route('inventario.cantidad', $ingrediente), ['cantidad_actual_almacen' => 25])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('exito', 'Cantidad del ingrediente actualizada correctamente.');

        $this->put(route('inventario.update', $ingrediente), [])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No tienes permiso para acceder a esa sección.');

        $this->get(route('platos.edit', $platoPropio))->assertOk();

        $this->get(route('platos.edit', $platoAjeno))
            ->assertRedirect(route('platos.index'))
            ->assertSessionHas('error', 'Solo puedes gestionar platillos de la categoría asignada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosReceta(): array
    {
        return [
            'receta' => [
                [
                    'id_ingrediente' => $this->ingrediente()->id_ingrediente,
                    'cantidad_requerida' => 1,
                    'unidad_medida' => 'kg',
                ],
            ],
            'pasos' => "Paso uno.\nPaso dos.",
        ];
    }
}
