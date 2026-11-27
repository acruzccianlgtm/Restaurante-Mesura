@extends('layouts.app')

@section('titulo', 'Encargados')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Encargados</h1>
            <p class="text-muted mb-0">Personas responsables de las categorías de la carta.</p>
        </div>
        @can('empleados.crear')
            <a href="{{ route('encargados.create') }}" class="btn btn-restaurante">Nuevo encargado</a>
        @endcan
    </div>

    @if ($encargados->isEmpty())
        <div class="card">
            <div class="card-body py-5 text-center">
                <p class="mb-3 text-muted">No hay encargados registrados.</p>
                @can('empleados.crear')
                    <a href="{{ route('encargados.create') }}" class="btn btn-restaurante">Crear el primer encargado</a>
                @endcan
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre completo</th>
                            <th>Cargo</th>
                            <th>Teléfono</th>
                            <th>Categorías a cargo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($encargados as $encargado)
                            <tr>
                                <td class="text-muted">{{ $encargado->id_encargado }}</td>
                                <td class="fw-semibold">{{ $encargado->nombre_completo }}</td>
                                <td>{{ $encargado->cargo ?: 'Sin cargo' }}</td>
                                <td>{{ $encargado->telefono ?: 'Sin teléfono' }}</td>
                                <td>
                                    @forelse ($encargado->categorias as $categoria)
                                        <span class="badge text-bg-light border">{{ $categoria->nombre }}</span>
                                    @empty
                                        <span class="text-muted">Sin categoría</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge {{ $encargado->estaActivo() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $encargado->estaActivo() ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('encargados.show', $encargado) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    @can('empleados.editar')
                                        @if ($encargado->estaActivo())
                                            <a href="{{ route('encargados.edit', $encargado) }}" class="btn btn-sm btn-outline-restaurante">Editar</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva el encargado para poder editarlo">Editar</button>
                                        @endif
                                    @endcan
                                    @can('empleados.estado')
                                        <form action="{{ route('encargados.estado', $encargado) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $encargado->estaActivo() ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                                {{ $encargado->estaActivo() ? 'Desactivar' : 'Reactivar' }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('empleados.eliminar')
                                        <a href="{{ route('encargados.eliminar', $encargado) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
