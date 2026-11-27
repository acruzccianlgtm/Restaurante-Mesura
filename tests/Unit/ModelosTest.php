<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ModelosTest extends TestCase
{
    public function test_usuario_activo_e_inactivo(): void
    {
        $activo = $this->usuario('cliente');
        $inactivo = $this->usuario('cliente', ['estado' => 'inactivo']);

        $this->assertTrue($activo->estaActivo());
        $this->assertFalse($inactivo->estaActivo());
    }

    public function test_usuario_identifica_cliente_y_encargado_de_categoria(): void
    {
        $cliente = $this->usuario('cliente');
        $encargado = $this->usuario('encargado de categoria');

        $this->assertTrue($cliente->esCliente());
        $this->assertFalse($cliente->esEncargadoDeCategoria());
        $this->assertTrue($encargado->esEncargadoDeCategoria());
        $this->assertFalse($encargado->esCliente());
    }

    public function test_nombre_completo_se_deriva_del_correo_o_de_atributos(): void
    {
        $usuario = $this->usuario('cliente', ['correo' => 'juan.perez@example.com']);

        $this->assertSame('Juan Perez', $usuario->nombre_completo);

        $usuario->nombre = 'Ana';
        $usuario->apellido = 'Pérez';

        $this->assertSame('Ana Pérez', $usuario->nombre_completo);
    }

    public function test_iniciales_del_usuario(): void
    {
        $usuario = $this->usuario('cliente', ['correo' => 'juan.perez@example.com']);

        $this->assertSame('J', $usuario->initials());
    }

    public function test_rol_de_usuario_se_sincroniza_con_spatie(): void
    {
        $usuario = $this->usuario('cocinero');

        $this->assertTrue($usuario->hasRole('cocinero'));
        $this->assertTrue($usuario->hasPermissionTo('inventario.ver'));
        $this->assertFalse($usuario->hasPermissionTo('platos.crear'));
    }

    public function test_usuario_con_rol_vacio_no_recibe_roles(): void
    {
        $usuario = User::factory()->create(['rol' => null]);

        $this->assertSame(0, $usuario->roles()->count());
    }

    public function test_nivel_de_ingrediente_segun_estado_y_cantidades(): void
    {
        $inactivo = $this->ingrediente(['estado' => 'inactivo']);
        $agotado = $this->ingrediente(['cantidad_actual_almacen' => 0]);
        $bajo = $this->ingrediente(['cantidad_actual_almacen' => 2, 'cantidad_minima' => 5]);
        $normal = $this->ingrediente(['cantidad_actual_almacen' => 10, 'cantidad_minima' => 5]);

        $this->assertSame('Inactivo', $inactivo->nivel);
        $this->assertSame('Agotado', $agotado->nivel);
        $this->assertSame('Bajo', $bajo->nivel);
        $this->assertSame('Normal', $normal->nivel);
    }

    public function test_relaciones_de_plato_categoria_y_encargado(): void
    {
        $encargado = $this->encargado(['nombre' => 'Relación']);
        $categoria = $this->categoria(['id_encargado' => $encargado->id_encargado]);
        $plato = $this->plato(['id_categoria' => $categoria->id_categoria]);

        $this->assertTrue($plato->estaActivo());
        $this->assertTrue($plato->categoria->is($categoria));
        $this->assertTrue($categoria->encargado->is($encargado));
        $this->assertSame(1, $categoria->platos()->count());
        $this->assertSame(1, $encargado->categorias()->count());
    }

    public function test_encargado_nombre_completo_y_estado(): void
    {
        $encargado = $this->encargado(['nombre' => 'María', 'apellido' => 'Gómez']);
        $inactivo = $this->encargado(['estado' => 'inactivo']);

        $this->assertSame('María Gómez', $encargado->nombre_completo);
        $this->assertTrue($encargado->estaActivo());
        $this->assertFalse($inactivo->estaActivo());
    }

    public function test_mesa_solo_devuelve_reservas_activas(): void
    {
        $mesa = $this->mesa();
        $activa = $this->reserva(['id_mesa' => $mesa->id_mesa]);
        $cancelada = $this->reserva(['id_mesa' => $mesa->id_mesa, 'estado' => 'cancelada']);

        $this->assertSame(2, $mesa->reservas()->count());
        $this->assertSame(1, $mesa->reservasActivas()->count());
        $this->assertTrue($mesa->reservasActivas()->first()->is($activa));
        $this->assertFalse($cancelada->estaActiva());
    }

    public function test_reserva_conoce_su_origen_y_mesa(): void
    {
        $mesa = $this->mesa();
        $manual = $this->reserva(['id_mesa' => $mesa->id_mesa, 'origen' => 'manual']);
        $desdeMesa = $this->reserva(['id_mesa' => $mesa->id_mesa, 'origen' => 'mesa']);

        $this->assertTrue($manual->estaActiva());
        $this->assertFalse($manual->esDesdeMesa());
        $this->assertTrue($desdeMesa->esDesdeMesa());
        $this->assertTrue($manual->mesa->is($mesa));
    }

    public function test_eliminar_categoria_con_plato_viola_clave_foranea(): void
    {
        $categoria = $this->categoria();
        $this->plato(['id_categoria' => $categoria->id_categoria]);

        $this->expectException(QueryException::class);

        Categoria::query()->whereKey($categoria->id_categoria)->delete();
    }
}
