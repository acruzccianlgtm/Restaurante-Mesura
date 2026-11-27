<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EncargadoPlatoController;
use App\Http\Controllers\IngredienteController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AccesoController::class, 'opciones'])->name('home');

Route::post('acceso/salir', [AccesoController::class, 'salir'])->name('acceso.salir');

Route::get('acceso/{tipo}', [AccesoController::class, 'formulario'])
    ->whereIn('tipo', ['cliente', 'empleado'])
    ->name('acceso.formulario');

Route::post('acceso/{tipo}', [AccesoController::class, 'ingresar'])
    ->whereIn('tipo', ['cliente', 'empleado'])
    ->name('acceso.ingresar');

Route::middleware('auth')->group(function () {
    Route::get('panel', [AdminController::class, 'panel'])->name('panel.index');

    Route::get('carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('carrito/tipo-servicio', [CarritoController::class, 'establecerTipoServicio'])->name('carrito.tipo-servicio');
    Route::post('carrito/{plato}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('carrito/{plato}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('carrito/{plato}', [CarritoController::class, 'quitar'])->name('carrito.quitar');
    Route::delete('carrito', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

    Route::middleware('permission:inventario.ver')->group(function () {
        Route::get('inventario', [IngredienteController::class, 'index'])->name('inventario.index');
    });

    Route::middleware('permission:inventario.crear')->group(function () {
        Route::post('inventario', [IngredienteController::class, 'store'])->name('inventario.store');
    });

    Route::middleware('permission:inventario.editar')->group(function () {
        Route::put('inventario/{ingrediente}', [IngredienteController::class, 'update'])->name('inventario.update');
    });

    Route::middleware('permission:inventario.cantidad')->group(function () {
        Route::patch('inventario/{ingrediente}/cantidad', [IngredienteController::class, 'actualizarCantidad'])->name('inventario.cantidad');
    });

    Route::middleware('permission:inventario.estado')->group(function () {
        Route::patch('inventario/{ingrediente}/estado', [IngredienteController::class, 'cambiarEstado'])->name('inventario.estado');
    });

    Route::resource('platos', PlatoController::class)
        ->only(['create', 'store'])
        ->middleware('permission:platos.crear');

    Route::resource('platos', PlatoController::class)
        ->only(['edit', 'update'])
        ->middleware('permission:platos.editar');

    Route::get('platos/{plato}/eliminar', [PlatoController::class, 'verEliminar'])
        ->middleware('permission:platos.eliminar')
        ->name('platos.eliminar');

    Route::resource('platos', PlatoController::class)
        ->only(['destroy'])
        ->middleware('permission:platos.eliminar');

    Route::resource('platos', PlatoController::class)
        ->only(['index', 'show'])
        ->middleware('permission:platos.ver');

    Route::patch('platos/{plato}/estado', [PlatoController::class, 'cambiarEstado'])
        ->middleware('permission:platos.estado')
        ->name('platos.estado');

    Route::middleware('permission:recetas.editar')->group(function () {
        Route::get('platos/{plato}/receta/edit', [PlatoController::class, 'editReceta'])
            ->name('platos.receta.edit');

        Route::put('platos/{plato}/receta', [PlatoController::class, 'updateReceta'])
            ->name('platos.receta.update');
    });

    Route::resource('categorias', CategoriaController::class)
        ->only(['create', 'store'])
        ->middleware('permission:categorias.crear');

    Route::resource('categorias', CategoriaController::class)
        ->only(['edit', 'update'])
        ->middleware('permission:categorias.editar');

    Route::get('categorias/{categoria}/eliminar', [CategoriaController::class, 'verEliminar'])
        ->middleware('permission:categorias.eliminar')
        ->name('categorias.eliminar');

    Route::resource('categorias', CategoriaController::class)
        ->only(['destroy'])
        ->middleware('permission:categorias.eliminar');

    Route::resource('categorias', CategoriaController::class)
        ->only(['index', 'show'])
        ->middleware('permission:categorias.ver');

    Route::patch('categorias/{categoria}/estado', [CategoriaController::class, 'cambiarEstado'])
        ->middleware('permission:categorias.estado')
        ->name('categorias.estado');

    Route::resource('encargados', EncargadoPlatoController::class)
        ->only(['create', 'store'])
        ->middleware('permission:empleados.crear');

    Route::resource('encargados', EncargadoPlatoController::class)
        ->only(['edit', 'update'])
        ->middleware('permission:empleados.editar');

    Route::get('encargados/{encargado}/eliminar', [EncargadoPlatoController::class, 'verEliminar'])
        ->middleware('permission:empleados.eliminar')
        ->name('encargados.eliminar');

    Route::resource('encargados', EncargadoPlatoController::class)
        ->only(['destroy'])
        ->middleware('permission:empleados.eliminar');

    Route::resource('encargados', EncargadoPlatoController::class)
        ->only(['index', 'show'])
        ->middleware('permission:empleados.ver');

    Route::patch('encargados/{encargado}/estado', [EncargadoPlatoController::class, 'cambiarEstado'])
        ->middleware('permission:empleados.estado')
        ->name('encargados.estado');

    Route::resource('usuarios', UsuarioController::class)
        ->only(['create', 'store'])
        ->middleware('permission:usuarios.crear');

    Route::resource('usuarios', UsuarioController::class)
        ->only(['edit', 'update'])
        ->middleware('permission:usuarios.editar');

    Route::resource('usuarios', UsuarioController::class)
        ->only(['index', 'show'])
        ->middleware('permission:usuarios.ver');

    Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])
        ->middleware('permission:usuarios.estado')
        ->name('usuarios.estado');

    Route::resource('mesas', MesaController::class)
        ->only(['create', 'store'])
        ->middleware('permission:mesas.crear');

    Route::resource('mesas', MesaController::class)
        ->only(['edit', 'update'])
        ->middleware('permission:mesas.editar');

    Route::get('mesas/{mesa}/eliminar', [MesaController::class, 'verEliminar'])
        ->middleware('permission:mesas.eliminar')
        ->name('mesas.eliminar');

    Route::resource('mesas', MesaController::class)
        ->only(['destroy'])
        ->middleware('permission:mesas.eliminar');

    Route::resource('mesas', MesaController::class)
        ->only(['index'])
        ->middleware('permission:mesas.ver');

    Route::patch('mesas/{mesa}/estado', [MesaController::class, 'cambiarEstado'])
        ->middleware('permission:mesas.estado')
        ->name('mesas.estado');

    Route::middleware('permission:reservas.crear')->group(function () {
        Route::get('mesas/reservas/create', [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('mesas/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    });

    Route::middleware('permission:reservas.editar')->group(function () {
        Route::get('mesas/reservas/{reserva}/edit', [ReservaController::class, 'edit'])->name('reservas.edit');
        Route::match(['put', 'patch'], 'mesas/reservas/{reserva}', [ReservaController::class, 'update'])->name('reservas.update');
    });

    Route::middleware('permission:reservas.cancelar')->group(function () {
        Route::patch('mesas/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
    });
});
