<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Crea los roles y permisos base del restaurante y sincroniza
     * el rol principal de los usuarios existentes.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'platos.ver',
            'platos.crear',
            'platos.editar',
            'platos.eliminar',
            'platos.estado',
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',
            'categorias.estado',
            'empleados.ver',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',
            'empleados.estado',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.estado',
            'inventario.ver',
            'inventario.crear',
            'inventario.editar',
            'inventario.cantidad',
            'inventario.estado',
            'mesas.ver',
            'mesas.crear',
            'mesas.editar',
            'mesas.eliminar',
            'mesas.estado',
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.cancelar',
            'pedidos.ver',
            'pedidos.gestionar',
            'pagos.ver',
            'pagos.gestionar',
            'recetas.ver',
            'recetas.editar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'cliente' => [
                'platos.ver',
                'categorias.ver',
            ],
            'mesero' => [
                'platos.ver',
                'categorias.ver',
                'mesas.ver',
                'mesas.estado',
                'reservas.ver',
            ],
            'cocinero' => [
                'platos.ver',
                'categorias.ver',
                'inventario.ver',
                'inventario.cantidad',
                'recetas.ver',
            ],
            'cajero' => [
                'platos.ver',
                'categorias.ver',
                'pedidos.ver',
                'pedidos.gestionar',
                'pagos.ver',
                'pagos.gestionar',
            ],
            'encargado de categoria' => [
                'platos.ver',
                'platos.crear',
                'platos.editar',
                'platos.eliminar',
                'categorias.ver',
                'inventario.ver',
                'inventario.cantidad',
                'recetas.ver',
                'recetas.editar',
            ],
            'jefe de cocina' => [
                'platos.ver',
                'platos.crear',
                'platos.editar',
                'platos.eliminar',
                'platos.estado',
                'categorias.ver',
                'categorias.crear',
                'categorias.editar',
                'categorias.eliminar',
                'categorias.estado',
                'empleados.ver',
                'empleados.estado',
                'usuarios.ver',
                'usuarios.crear',
                'usuarios.editar',
                'usuarios.estado',
                'inventario.ver',
                'inventario.crear',
                'inventario.editar',
                'inventario.estado',
                'mesas.ver',
                'mesas.crear',
                'mesas.editar',
                'mesas.eliminar',
                'reservas.ver',
                'reservas.crear',
                'reservas.editar',
                'reservas.cancelar',
                'pedidos.ver',
                'recetas.ver',
                'recetas.editar',
            ],
            'encargado' => array_values(array_diff($permisos, [
                'mesas.estado',
                'recetas.ver',
                'recetas.editar',
            ])),
        ];

        foreach ($roles as $nombre => $permisosRol) {
            $rol = Role::firstOrCreate([
                'name' => $nombre,
                'guard_name' => 'web',
            ]);

            $rol->syncPermissions($permisosRol);
        }

        User::firstOrCreate(
            ['correo' => 'cocina@restaurante.com'],
            [
                'password' => Hash::make('password123'),
                'rol' => 'cocinero',
                'estado' => 'activo',
            ],
        );

        User::query()
            ->whereNotNull('rol')
            ->each(function (User $user): void {
                $existe = Role::query()
                    ->where('name', $user->rol)
                    ->where('guard_name', 'web')
                    ->exists();

                if ($existe) {
                    $user->syncRoles([$user->rol]);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
