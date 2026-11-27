<?php

namespace App\Http\Controllers;

use App\Models\Ingrediente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredienteController extends Controller
{
    public function index(): View
    {
        return view('inventario.index', [
            'ingredientes' => Ingrediente::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $datos['estado'] = 'activo';

        Ingrediente::query()->create($datos);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Ingrediente agregado al inventario.');
    }

    public function update(Request $request, Ingrediente $ingrediente): RedirectResponse
    {
        if (! $ingrediente->estaActivo()) {
            return redirect()
                ->route('inventario.index')
                ->with('error', 'Reactiva el ingrediente antes de editarlo.');
        }

        $ingrediente->update($this->validar($request, $ingrediente));

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Ingrediente actualizado correctamente.');
    }

    public function actualizarCantidad(Request $request, Ingrediente $ingrediente): RedirectResponse
    {
        if (! $ingrediente->estaActivo()) {
            return redirect()
                ->route('inventario.index')
                ->with('error', 'Reactiva el ingrediente antes de editar su cantidad.');
        }

        $datos = $request->validate([
            'cantidad_actual_almacen' => ['required', 'numeric', 'min:0'],
        ], [
            'cantidad_actual_almacen.required' => 'La cantidad actual es obligatoria.',
            'cantidad_actual_almacen.numeric' => 'La cantidad actual debe ser un número.',
            'cantidad_actual_almacen.min' => 'La cantidad actual no puede ser negativa.',
        ]);

        $ingrediente->update($datos);

        return redirect()
            ->route('inventario.index')
            ->with('exito', 'Cantidad del ingrediente actualizada correctamente.');
    }

    public function cambiarEstado(Ingrediente $ingrediente): RedirectResponse
    {
        $ingrediente->estado = $ingrediente->estaActivo() ? 'inactivo' : 'activo';
        $ingrediente->save();

        return redirect()
            ->route('inventario.index')
            ->with('exito', $ingrediente->estaActivo() ? 'Ingrediente reactivado.' : 'Ingrediente desactivado.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request, ?Ingrediente $ingrediente = null): array
    {
        $reglaNombre = Rule::unique('ingrediente', 'nombre');

        if ($ingrediente !== null) {
            $reglaNombre->ignore($ingrediente->id_ingrediente, 'id_ingrediente');
        }

        return $request->validate([
            'nombre' => ['required', 'string', 'max:120', $reglaNombre],
            'cantidad_actual_almacen' => ['required', 'numeric', 'min:0'],
            'unidad_medida_almacen' => ['required', 'string', 'max:20'],
            'cantidad_minima' => ['required', 'numeric', 'min:0'],
        ], [
            'nombre.required' => 'El nombre del ingrediente es obligatorio.',
            'nombre.unique' => 'Ya existe un ingrediente con ese nombre.',
            'cantidad_actual_almacen.required' => 'La cantidad actual es obligatoria.',
            'cantidad_actual_almacen.numeric' => 'La cantidad actual debe ser un número.',
            'cantidad_actual_almacen.min' => 'La cantidad actual no puede ser negativa.',
            'unidad_medida_almacen.required' => 'La unidad de medida es obligatoria.',
            'cantidad_minima.required' => 'La cantidad mínima es obligatoria.',
            'cantidad_minima.numeric' => 'La cantidad mínima debe ser un número.',
            'cantidad_minima.min' => 'La cantidad mínima no puede ser negativa.',
        ]);
    }
}
