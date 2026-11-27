<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\EncargadoPlato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::with('encargado')
            ->withCount('platos')
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        $encargados = EncargadoPlato::orderBy('nombre')->orderBy('apellido')->get();

        return view('categorias.create', compact('encargados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:categoria,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'id_encargado' => ['required', 'integer', 'exists:encargado_plato,id_encargado'],
        ]);

        Categoria::create($datos);

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'La categoría se registró correctamente.');
    }

    public function show(Categoria $categoria): View
    {
        $categoria->load('encargado')->loadCount('platos');

        return view('categorias.detalles', compact('categoria'));
    }

    public function edit(Categoria $categoria): View|RedirectResponse
    {
        if (! $categoria->estaActivo()) {
            return redirect()
                ->route('categorias.index')
                ->with('error', 'Reactiva la categoría antes de editarla.');
        }

        $encargados = EncargadoPlato::orderBy('nombre')->orderBy('apellido')->get();

        return view('categorias.edit', compact('categoria', 'encargados'));
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        if (! $categoria->estaActivo()) {
            return redirect()
                ->route('categorias.index')
                ->with('error', 'Reactiva la categoría antes de editarla.');
        }

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categoria', 'nombre')->ignore($categoria->id_categoria, 'id_categoria'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'id_encargado' => ['required', 'integer', 'exists:encargado_plato,id_encargado'],
        ]);

        $categoria->update($datos);

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'La categoría se actualizó correctamente.');
    }

    public function cambiarEstado(Categoria $categoria): RedirectResponse
    {
        $categoria->estado = $categoria->estaActivo() ? 'inactivo' : 'activo';
        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with('exito', $categoria->estaActivo() ? 'Categoría reactivada.' : 'Categoría desactivada.');
    }

    public function verEliminar(Categoria $categoria): View
    {
        $categoria->load('encargado')->loadCount('platos');

        return view('categorias.eliminar', compact('categoria'));
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->platos()->exists()) {
            return redirect()
                ->route('categorias.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene platillos registrados.');
        }

        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('exito', 'La categoría se eliminó correctamente.');
    }
}
