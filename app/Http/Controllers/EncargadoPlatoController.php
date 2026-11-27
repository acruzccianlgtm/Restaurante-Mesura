<?php

namespace App\Http\Controllers;

use App\Models\EncargadoPlato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EncargadoPlatoController extends Controller
{
    public function index(): View
    {
        $encargados = EncargadoPlato::with('categorias')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        return view('encargados.index', compact('encargados'));
    }

    public function create(): View
    {
        return view('encargados.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'edad' => ['nullable', 'integer', 'min:16', 'max:100'],
            'cargo' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'ciudad' => ['nullable', 'string', 'max:40'],
        ]);

        EncargadoPlato::create($datos);

        return redirect()
            ->route('encargados.index')
            ->with('exito', 'El encargado se registró correctamente.');
    }

    public function show(EncargadoPlato $encargado): View
    {
        $encargado->load('categorias');

        return view('encargados.detalles', compact('encargado'));
    }

    public function edit(EncargadoPlato $encargado): View|RedirectResponse
    {
        if (! $encargado->estaActivo()) {
            return redirect()
                ->route('encargados.index')
                ->with('error', 'Reactiva el encargado antes de editarlo.');
        }

        return view('encargados.edit', compact('encargado'));
    }

    public function update(Request $request, EncargadoPlato $encargado): RedirectResponse
    {
        if (! $encargado->estaActivo()) {
            return redirect()
                ->route('encargados.index')
                ->with('error', 'Reactiva el encargado antes de editarlo.');
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'edad' => ['nullable', 'integer', 'min:16', 'max:100'],
            'cargo' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'ciudad' => ['nullable', 'string', 'max:40'],
        ]);

        $encargado->update($datos);

        return redirect()
            ->route('encargados.index')
            ->with('exito', 'El encargado se actualizó correctamente.');
    }

    public function cambiarEstado(EncargadoPlato $encargado): RedirectResponse
    {
        $encargado->estado = $encargado->estaActivo() ? 'inactivo' : 'activo';
        $encargado->save();

        return redirect()
            ->route('encargados.index')
            ->with('exito', $encargado->estaActivo() ? 'Encargado reactivado.' : 'Encargado desactivado.');
    }

    public function verEliminar(EncargadoPlato $encargado): View
    {
        $encargado->load('categorias');

        return view('encargados.eliminar', compact('encargado'));
    }

    public function destroy(EncargadoPlato $encargado): RedirectResponse
    {
        if ($encargado->categorias()->exists()) {
            return redirect()
                ->route('encargados.index')
                ->with('error', 'No se puede eliminar el encargado porque tiene categorías asignadas.');
        }

        $encargado->delete();

        return redirect()
            ->route('encargados.index')
            ->with('exito', 'El encargado se eliminó correctamente.');
    }
}
