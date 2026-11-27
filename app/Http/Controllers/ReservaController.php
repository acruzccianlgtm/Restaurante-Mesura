<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function create(): View
    {
        return view('reservas.create', [
            'mesas' => Mesa::orderBy('id_mesa')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        if (! $this->capacidadValida($datos)) {
            return back()->withInput()->with('error', 'La cantidad de personas no puede superar la capacidad de la mesa.');
        }

        Reserva::create($datos + ['estado' => 'activa']);

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La reserva se creó correctamente.');
    }

    public function edit(Reserva $reserva): View|RedirectResponse
    {
        $redireccion = $this->verificarActiva($reserva);

        if ($redireccion !== null) {
            return $redireccion;
        }

        return view('reservas.edit', [
            'reserva' => $reserva,
            'mesas' => Mesa::orderBy('id_mesa')->get(),
        ]);
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        $redireccion = $this->verificarActiva($reserva);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $datos = $this->validar($request);

        if (! $this->capacidadValida($datos)) {
            return back()->withInput()->with('error', 'La cantidad de personas no puede superar la capacidad de la mesa.');
        }

        $reserva->update($datos);

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La reserva se actualizó correctamente.');
    }

    public function cancelar(Reserva $reserva): RedirectResponse
    {
        $redireccion = $this->verificarActiva($reserva);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $reserva->update(['estado' => 'cancelada']);

        return redirect()
            ->route('mesas.index')
            ->with('exito', 'La reserva se canceló correctamente.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request): array
    {
        return $request->validate([
            'id_mesa' => ['required', 'integer', 'exists:mesa,id_mesa'],
            'nombre_cliente' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_reserva' => ['required', 'date'],
            'hora_reserva' => ['required', 'date_format:H:i'],
            'cantidad_personas' => ['required', 'integer', 'min:1'],
        ], [
            'id_mesa.required' => 'Selecciona una mesa.',
            'id_mesa.exists' => 'La mesa seleccionada no existe.',
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'fecha_reserva.required' => 'La fecha es obligatoria.',
            'fecha_reserva.date' => 'La fecha no es válida.',
            'hora_reserva.required' => 'La hora es obligatoria.',
            'hora_reserva.date_format' => 'La hora debe tener el formato HH:MM.',
            'cantidad_personas.required' => 'La cantidad de personas es obligatoria.',
            'cantidad_personas.integer' => 'La cantidad de personas debe ser un número entero.',
            'cantidad_personas.min' => 'La cantidad mínima es 1 persona.',
        ]);
    }

    /** @param array<string, mixed> $datos */
    private function capacidadValida(array $datos): bool
    {
        $mesa = Mesa::query()->where('id_mesa', $datos['id_mesa'])->first();

        return $mesa !== null && (int) $datos['cantidad_personas'] <= $mesa->capacidad;
    }

    private function verificarActiva(Reserva $reserva): ?RedirectResponse
    {
        if ($reserva->estaActiva()) {
            return null;
        }

        return redirect()
            ->route('mesas.index')
            ->with('error', 'La reserva está cancelada y no se puede editar.');
    }
}
