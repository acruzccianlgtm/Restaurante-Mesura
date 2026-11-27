<?php

namespace Tests\Unit;

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\RedirectResponse;
use ReflectionMethod;
use Tests\TestCase;

class LogicaInternaTest extends TestCase
{
    public function test_claves_de_sesion_del_carrito_usan_el_usuario_autenticado(): void
    {
        $usuario = $this->usuario('cliente');
        $this->actingAs($usuario);

        $controlador = new CarritoController;

        $this->assertSame(
            'carrito_'.$usuario->id,
            $this->metodoPrivado($controlador, 'clave')->invoke($controlador)
        );
        $this->assertSame(
            'tipo_servicio_'.$usuario->id,
            $this->metodoPrivado($controlador, 'claveTipoServicio')->invoke($controlador)
        );
    }

    public function test_carrito_sanea_ids_y_cantidades_invalidos(): void
    {
        $usuario = $this->usuario('cliente');
        $this->actingAs($usuario);

        session()->put('carrito_'.$usuario->id, [
            5 => 3,
            'x' => 2,
            6 => -1,
            7 => 'abc',
        ]);

        $controlador = new CarritoController;
        $resultado = $this->metodoPrivado($controlador, 'carrito')->invoke($controlador);

        $this->assertSame([5 => 3], $resultado);
    }

    public function test_tipo_de_servicio_tiene_valor_por_defecto_y_fallback(): void
    {
        $usuario = $this->usuario('cliente');
        $this->actingAs($usuario);

        $controlador = new CarritoController;
        $metodo = $this->metodoPrivado($controlador, 'tipoServicio');

        $this->assertSame('recoger', $metodo->invoke($controlador));

        session()->put('tipo_servicio_'.$usuario->id, 'domicilio');
        $this->assertSame('domicilio', $metodo->invoke($controlador));

        session()->put('tipo_servicio_'.$usuario->id, 'invalido');
        $this->assertSame('recoger', $metodo->invoke($controlador));
    }

    public function test_capacidad_valida_de_reserva_es_exacta(): void
    {
        $mesa = $this->mesa(['capacidad' => 4]);
        $controlador = new ReservaController;
        $metodo = $this->metodoPrivado($controlador, 'capacidadValida');

        $this->assertTrue($metodo->invoke($controlador, [
            'id_mesa' => $mesa->id_mesa,
            'cantidad_personas' => 4,
        ]));
        $this->assertFalse($metodo->invoke($controlador, [
            'id_mesa' => $mesa->id_mesa,
            'cantidad_personas' => 5,
        ]));
        $this->assertFalse($metodo->invoke($controlador, [
            'id_mesa' => 99999,
            'cantidad_personas' => 1,
        ]));
    }

    public function test_verificar_categoria_de_plato_devuelve_null_o_redirect(): void
    {
        $propia = $this->categoria(['nombre' => 'Propia']);
        $ajena = $this->categoria(['nombre' => 'Ajena']);
        $this->actingAs($this->usuario('encargado de categoria', [
            'id_categoria' => $propia->id_categoria,
        ]));

        $controlador = new PlatoController;
        $metodo = $this->metodoPrivado($controlador, 'verificarCategoria');

        $this->assertNull($metodo->invoke($controlador, $propia->id_categoria));

        $respuesta = $metodo->invoke($controlador, $ajena->id_categoria);

        $this->assertInstanceOf(RedirectResponse::class, $respuesta);
        $this->assertSame(route('platos.index'), $respuesta->getTargetUrl());
    }

    public function test_roles_disponibles_excluye_encargado_del_seed(): void
    {
        $controlador = new UsuarioController;
        $roles = $this->metodoPrivado($controlador, 'rolesDisponibles')->invoke($controlador);

        $this->assertContains('cliente', $roles);
        $this->assertContains('mesero', $roles);
        $this->assertContains('cocinero', $roles);
        $this->assertContains('cajero', $roles);
        $this->assertContains('encargado de categoria', $roles);
        $this->assertContains('jefe de cocina', $roles);
        $this->assertNotContains('encargado', $roles);
    }

    private function metodoPrivado(object $controlador, string $nombre): ReflectionMethod
    {
        $metodo = new ReflectionMethod($controlador, $nombre);
        $metodo->setAccessible(true);

        return $metodo;
    }
}
