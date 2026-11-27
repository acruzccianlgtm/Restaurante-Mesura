<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Ingrediente;
use App\Models\Plato;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatoController extends Controller
{
    public function index(): View
    {
        $platos = Plato::with('categoria')->orderBy('nombre')->get();

        return view('platos.index', compact('platos'));
    }

    public function create(): View
    {
        $consulta = Categoria::with('encargado');

        if ($this->esEncargadoDeCategoria()) {
            $consulta->where('id_categoria', $this->categoriaAsignada());
        }

        return view('platos.create', [
            'categorias' => $consulta->orderBy('nombre')->get(),
            'ingredientes' => $this->ingredientesDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:plato,nombre'],
            'descripcion' => ['nullable', 'string'],
            'nivel_dificultad' => ['required', 'integer', 'min:1', 'max:5'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'id_categoria' => ['required', 'integer', 'exists:categoria,id_categoria'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'pasos' => ['required', 'string', 'max:5000'],
            'receta' => ['required', 'array', 'min:1'],
            'receta.*.id_ingrediente' => ['required', 'integer', 'distinct', 'exists:ingrediente,id_ingrediente'],
            'receta.*.cantidad_requerida' => ['required', 'numeric', 'min:0.001'],
            'receta.*.unidad_medida' => ['required', 'string', 'max:30'],
        ]);

        $redireccion = $this->verificarCategoria((int) $datos['id_categoria']);

        if ($redireccion !== null) {
            return $redireccion;
        }

        if ($request->hasFile('foto')) {
            $datos['foto'] = $this->guardarFoto($request->file('foto'), $datos['nombre']);
        }

        $filas = (array) $datos['receta'];
        $pasos = (string) $datos['pasos'];

        unset($datos['receta'], $datos['pasos']);

        DB::transaction(function () use ($datos, $filas, $pasos): void {
            $plato = Plato::create($datos);
            $this->guardarReceta($plato->id_plato, $filas, $pasos);
        });

        return redirect()
            ->route('platos.index')
            ->with('exito', 'El platillo se agregó correctamente a la carta.');
    }

    public function show(Plato $plato): View
    {
        $plato->load(['categoria.encargado', 'recetas.ingrediente']);

        return view('platos.detalles', compact('plato'));
    }

    public function edit(Plato $plato): View|RedirectResponse
    {
        if (! $plato->estaActivo()) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'Reactiva el platillo antes de editarlo.');
        }

        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $consulta = Categoria::with('encargado');

        if ($this->esEncargadoDeCategoria()) {
            $consulta->where('id_categoria', $this->categoriaAsignada());
        }

        return view('platos.edit', [
            'plato' => $plato,
            'categorias' => $consulta->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Plato $plato): RedirectResponse
    {
        if (! $plato->estaActivo()) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'Reactiva el platillo antes de editarlo.');
        }

        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('plato', 'nombre')->ignore($plato->id_plato, 'id_plato'),
            ],
            'descripcion' => ['nullable', 'string'],
            'nivel_dificultad' => ['required', 'integer', 'min:1', 'max:5'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'id_categoria' => ['required', 'integer', 'exists:categoria,id_categoria'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'eliminar_foto' => ['nullable', 'boolean'],
        ]);

        $redireccion = $this->verificarCategoria((int) $datos['id_categoria']);

        if ($redireccion !== null) {
            return $redireccion;
        }

        if ($request->hasFile('foto')) {
            $this->eliminarFoto($plato);
            $datos['foto'] = $this->guardarFoto($request->file('foto'), $datos['nombre']);
        } elseif ($request->boolean('eliminar_foto')) {
            $this->eliminarFoto($plato);
            $datos['foto'] = null;
        }

        unset($datos['eliminar_foto']);

        $plato->update($datos);

        return redirect()
            ->route('platos.index')
            ->with('exito', 'El platillo se actualizó correctamente.');
    }

    public function editReceta(Plato $plato): View|RedirectResponse
    {
        if (! $plato->estaActivo()) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'Reactiva el platillo antes de editar su receta.');
        }

        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        return view('platos.receta', [
            'plato' => $plato->load('recetas.ingrediente'),
            'ingredientes' => $this->ingredientesDisponibles(),
        ]);
    }

    public function updateReceta(Request $request, Plato $plato): RedirectResponse
    {
        if (! $plato->estaActivo()) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'Reactiva el platillo antes de editar su receta.');
        }

        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $datos = $request->validate([
            'pasos' => ['required', 'string', 'max:5000'],
            'receta' => ['required', 'array', 'min:1'],
            'receta.*.id_ingrediente' => ['required', 'integer', 'distinct', 'exists:ingrediente,id_ingrediente'],
            'receta.*.cantidad_requerida' => ['required', 'numeric', 'min:0.001'],
            'receta.*.unidad_medida' => ['required', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($plato, $datos): void {
            $this->guardarReceta($plato->id_plato, (array) $datos['receta'], (string) $datos['pasos']);
        });

        return redirect()
            ->route('platos.show', $plato)
            ->with('exito', 'La receta se actualizó correctamente.');
    }

    public function cambiarEstado(Plato $plato): RedirectResponse
    {
        $plato->estado = $plato->estaActivo() ? 'inactivo' : 'activo';
        $plato->save();

        return redirect()
            ->route('platos.index')
            ->with('exito', $plato->estaActivo() ? 'Platillo reactivado.' : 'Platillo desactivado.');
    }

    public function verEliminar(Plato $plato): View|RedirectResponse
    {
        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $plato->load('categoria');

        return view('platos.eliminar', compact('plato'));
    }

    public function destroy(Plato $plato): RedirectResponse
    {
        $redireccion = $this->verificarCategoria($plato->id_categoria);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $tieneRelaciones = DB::table('receta')->where('id_plato', $plato->id_plato)->exists()
            || DB::table('detalle_pedido')->where('id_plato', $plato->id_plato)->exists();

        if ($tieneRelaciones) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'No se puede eliminar el platillo porque tiene recetas o pedidos relacionados.');
        }

        $plato->delete();

        $this->eliminarFoto($plato);

        return redirect()
            ->route('platos.index')
            ->with('exito', 'El platillo se eliminó de la carta.');
    }

    private function usuarioActual(): ?User
    {
        $usuario = auth()->user();

        return $usuario instanceof User ? $usuario : null;
    }

    private function esEncargadoDeCategoria(): bool
    {
        return $this->usuarioActual()?->esEncargadoDeCategoria() === true;
    }

    private function categoriaAsignada(): ?int
    {
        return $this->usuarioActual()?->id_categoria;
    }

    private function verificarCategoria(int $idCategoria): ?RedirectResponse
    {
        $usuario = $this->usuarioActual();

        if ($usuario === null || $usuario->puedeGestionarCategoria($idCategoria)) {
            return null;
        }

        return redirect()
            ->route('platos.index')
            ->with('error', 'Solo puedes gestionar platillos de la categoría asignada.');
    }

    /**
     * @return Collection<int, Ingrediente>
     */
    private function ingredientesDisponibles(): Collection
    {
        return Ingrediente::query()
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function guardarReceta(int $idPlato, array $filas, string $pasos): void
    {
        DB::table('receta')->where('id_plato', $idPlato)->delete();

        foreach ($filas as $fila) {
            DB::table('receta')->insert([
                'id_plato' => $idPlato,
                'id_ingrediente' => (int) $fila['id_ingrediente'],
                'cantidad_requerida' => $fila['cantidad_requerida'],
                'unidad_medida' => $fila['unidad_medida'],
                'pasos' => $pasos,
            ]);
        }
    }

    private function guardarFoto(UploadedFile $archivo, string $nombre): string
    {
        $nombreArchivo = Str::slug($nombre).'-'.time().'.'.$archivo->getClientOriginalExtension();
        $archivo->move(public_path('uploads/platos'), $nombreArchivo);

        return 'uploads/platos/'.$nombreArchivo;
    }

    private function eliminarFoto(Plato $plato): void
    {
        if ($plato->foto && file_exists(public_path($plato->foto))) {
            unlink(public_path($plato->foto));
        }
    }
}
