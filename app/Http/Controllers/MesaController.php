<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function index(): View
    {
        $mesas = Mesa::with('reservasActivas')->orderBy('id_mesa')->get();
        $reservas = Reserva::with('mesa')
            ->where('estado', 'activa')
            ->orderBy('fecha_reserva')
            ->orderBy('hora_reserva')
            ->get();

        return view('mesas.index', compact('mesas', 'reservas'));
    }

    public function create(): View
    {
        return view('mesas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'capacidad' => ['required', 'integer', 'min:1', 'max:30'],
        ], [
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.integer' => 'La capacidad debe ser un número entero.',
            'capacidad.min' => 'La capacidad mínima es 1 persona.',
            'capacidad.max' => 'La capacidad máxima es 30 personas.',
        ]);

        Mesa::create($datos + ['estado' => 'disponible']);

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La mesa se creó correctamente.');
    }

    public function edit(Mesa $mesa): View
    {
        return view('mesas.edit', compact('mesa'));
    }

    public function update(Request $request, Mesa $mesa): RedirectResponse
    {
        $datos = $request->validate([
            'capacidad' => ['required', 'integer', 'min:1', 'max:30'],
        ], [
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.integer' => 'La capacidad debe ser un número entero.',
            'capacidad.min' => 'La capacidad mínima es 1 persona.',
            'capacidad.max' => 'La capacidad máxima es 30 personas.',
        ]);

        $mesa->update($datos);

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La capacidad de la mesa se actualizó correctamente.');
    }

    public function verEliminar(Mesa $mesa): View
    {
        $mesa->loadCount('reservas');

        return view('mesas.eliminar', compact('mesa'));
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        $mesa->delete();

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La mesa se eliminó correctamente.');
    }

    public function cambiarEstado(Request $request, Mesa $mesa): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['disponible', 'reservada', 'ocupada'])],
        ], [
            'estado.required' => 'Selecciona un estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        $mesa->update($datos);

        if ($datos['estado'] === 'reservada' && ! $mesa->reservasActivas()->exists()) {
            Reserva::create([
                'id_mesa' => $mesa->id_mesa,
                'nombre_cliente' => 'Reserva de mesa',
                'telefono' => null,
                'fecha_reserva' => now()->toDateString(),
                'hora_reserva' => now()->format('H:i'),
                'cantidad_personas' => $mesa->capacidad,
                'estado' => 'activa',
                'origen' => 'mesa',
            ]);
        }

        if (in_array($datos['estado'], ['disponible', 'ocupada'], true)) {
            $mesa->reservasActivas()
                ->where('origen', 'mesa')
                ->update(['estado' => 'cancelada']);
        }

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'El estado de la mesa se actualizó correctamente.');
    }
}
