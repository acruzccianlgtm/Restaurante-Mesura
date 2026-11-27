<?php

namespace Tests;

use App\Models\Categoria;
use App\Models\EncargadoPlato;
use App\Models\Ingrediente;
use App\Models\Mesa;
use App\Models\Plato;
use App\Models\Reserva;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->usaEsquemaSqliteDePrueba()) {
            return;
        }

        config()->set('database.connections.sqlite.foreign_key_constraints', true);
        DB::statement('PRAGMA foreign_keys = ON');
        DB::unprepared(file_get_contents(__DIR__.'/Support/sql/sqlite-domain-schema.sql'));

        DB::table('usuarios')->insert([
            'id' => 0,
            'correo' => 'sistema@test.local',
            'password' => Hash::make('no-login'),
            'rol' => null,
            'estado' => 'activo',
        ]);

        DB::table('encargado_plato')->insert([
            'user_id' => 0,
            'nombre' => 'Sin asignar',
            'apellido' => '',
            'estado' => 'activo',
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function usuario(string $rol = 'cliente', array $atributos = []): User
    {
        return User::factory()->create(array_merge([
            'rol' => $rol,
            'estado' => 'activo',
        ], $atributos));
    }

    protected function encargado(array $atributos = []): EncargadoPlato
    {
        return EncargadoPlato::create(array_merge([
            'nombre' => 'Encargado',
            'apellido' => 'Prueba',
            'cargo' => 'Encargado de categoria',
            'estado' => 'activo',
        ], $atributos));
    }

    protected function categoria(array $atributos = []): Categoria
    {
        return Categoria::create(array_merge([
            'nombre' => 'Categoria '.Str::upper(Str::random(6)),
            'descripcion' => 'Categoria de prueba',
            'id_encargado' => EncargadoPlato::query()->min('id_encargado'),
            'estado' => 'activo',
        ], $atributos));
    }

    protected function plato(array $atributos = []): Plato
    {
        $idCategoria = Categoria::query()->min('id_categoria');

        if ($idCategoria === null) {
            $idCategoria = $this->categoria()->getKey();
        }

        return Plato::create(array_merge([
            'nombre' => 'Plato '.Str::upper(Str::random(6)),
            'descripcion' => 'Plato de prueba',
            'nivel_dificultad' => 1,
            'precio_final' => 10.00,
            'id_categoria' => (int) $idCategoria,
            'estado' => 'activo',
        ], $atributos));
    }

    protected function ingrediente(array $atributos = []): Ingrediente
    {
        return Ingrediente::create(array_merge([
            'nombre' => 'Ingrediente '.Str::upper(Str::random(6)),
            'unidad_medida_almacen' => 'kg',
            'cantidad_actual_almacen' => 10,
            'cantidad_minima' => 2,
            'estado' => 'activo',
        ], $atributos));
    }

    protected function mesa(array $atributos = []): Mesa
    {
        return Mesa::create(array_merge([
            'capacidad' => 4,
            'estado' => 'disponible',
        ], $atributos));
    }

    protected function reserva(array $atributos = []): Reserva
    {
        $idMesa = Mesa::query()->min('id_mesa');

        if ($idMesa === null) {
            $idMesa = $this->mesa()->getKey();
        }

        return Reserva::create(array_merge([
            'id_mesa' => (int) $idMesa,
            'nombre_cliente' => 'Cliente Prueba',
            'telefono' => '600000000',
            'fecha_reserva' => now()->toDateString(),
            'hora_reserva' => '20:00',
            'cantidad_personas' => 2,
            'estado' => 'activa',
            'origen' => 'manual',
        ], $atributos));
    }

    private function usaEsquemaSqliteDePrueba(): bool
    {
        return app()->environment('testing')
            && config('database.default') === 'sqlite'
            && config('database.connections.sqlite.database') === ':memory:';
    }
}
