@extends('layouts.app')

@section('titulo', 'Mesas')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Mesas</h1>
            <p class="text-muted mb-0">Administra la capacidad, el estado y las reservas del salón.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('reservas.crear')
                <a href="{{ route('reservas.create') }}" class="btn btn-outline-restaurante">Nueva reserva</a>
            @endcan
            @can('mesas.crear')
                <a href="{{ route('mesas.create') }}" class="btn btn-restaurante">Nueva mesa</a>
            @endcan
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge text-bg-light border">Total: {{ $mesas->count() }}</span>
        <span class="badge text-bg-success">Disponibles: {{ $mesas->where('estado', 'disponible')->count() }}</span>
        <span class="badge text-bg-warning">Reservadas: {{ $mesas->where('estado', 'reservada')->count() }}</span>
        <span class="badge text-bg-danger">Ocupadas: {{ $mesas->where('estado', 'ocupada')->count() }}</span>
    </div>

    @if ($mesas->isEmpty())
        <div class="card">
            <div class="card-body py-5 text-center">
                <p class="mb-3 text-muted">No hay mesas registradas.</p>
                @can('mesas.crear')
                    <a href="{{ route('mesas.create') }}" class="btn btn-restaurante">Crear la primera mesa</a>
                @endcan
            </div>
        </div>
    @else
        <div class="card mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Capacidad (personas)</th>
                            <th>Estado</th>
                            <th>Reserva activa</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mesas as $mesa)
                            <tr>
                                <td class="fw-semibold">Mesa {{ $mesa->id_mesa }}</td>
                                <td>{{ $mesa->capacidad }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $mesa->estado === 'disponible' ? 'success' : ($mesa->estado === 'reservada' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($mesa->estado) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($mesa->reservasActivas->isNotEmpty())
                                        <span class="small">{{ $mesa->reservasActivas->first()->nombre_cliente }}</span>
                                    @else
                                        <span class="text-muted small">Sin reserva</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @can('mesas.editar')
                                        <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-sm btn-outline-restaurante">Editar capacidad</a>
                                    @endcan
                                    @can('mesas.eliminar')
                                        <a href="{{ route('mesas.eliminar', $mesa) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                                    @endcan
                                    @can('mesas.estado')
                                        <form action="{{ route('mesas.estado', $mesa) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="estado" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()" aria-label="Estado de la mesa {{ $mesa->id_mesa }}">
                                                @foreach (['disponible', 'reservada', 'ocupada'] as $estado)
                                                    <option value="{{ $estado }}" @selected($mesa->estado === $estado)>{{ ucfirst($estado) }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @can('reservas.ver')
        <div class="card">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <h2 class="h5 mb-0">Reservas activas</h2>
                @can('reservas.crear')
                    <a href="{{ route('reservas.create') }}" class="btn btn-sm btn-outline-restaurante">Nueva reserva</a>
                @endcan
            </div>
            @if ($reservas->isEmpty())
                <div class="card-body py-4 text-center text-muted">No hay reservas activas.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Mesa</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th class="text-center">Personas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservas as $reserva)
                                <tr>
                                    <td class="text-muted">{{ $reserva->id_reserva }}</td>
                                    <td class="fw-semibold">Mesa {{ $reserva->mesa?->id_mesa }}</td>
                                    <td>
                                        {{ $reserva->nombre_cliente }}
                                        @if ($reserva->esDesdeMesa())
                                            <span class="badge text-bg-info">Desde mesa</span>
                                        @endif
                                    </td>
                                    <td>{{ $reserva->telefono ?: '—' }}</td>
                                    <td>{{ $reserva->fecha_reserva }}</td>
                                    <td>{{ $reserva->hora_reserva }}</td>
                                    <td class="text-center">{{ $reserva->cantidad_personas }}</td>
                                    <td class="text-end text-nowrap">
                                        @can('reservas.editar')
                                            <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-sm btn-outline-restaurante">Editar</a>
                                        @endcan
                                        @can('reservas.cancelar')
                                            <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Cancelar esta reserva?')">Cancelar</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endcan
@endsection
