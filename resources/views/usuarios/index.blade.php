@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Administra los accesos y el estado de los usuarios del sistema.</p>
        </div>
        @can('usuarios.crear')
            <a href="{{ route('usuarios.create') }}" class="btn btn-restaurante">Nuevo usuario</a>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="text-muted">{{ $usuario->id }}</td>
                            <td>{{ $usuario->nombre_completo }}</td>
                            <td class="fw-semibold">{{ $usuario->correo }}</td>
                            <td class="text-capitalize">{{ $usuario->rol }}</td>
                            <td>
                                <span class="badge {{ $usuario->estaActivo() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $usuario->estaActivo() ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @can('usuarios.ver')
                                    <a href="{{ route('usuarios.show', $usuario) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                @endcan
                                @can('usuarios.editar')
                                    @if ($usuario->estaActivo())
                                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-restaurante">Editar</a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva el usuario para poder editarlo">Editar</button>
                                    @endif
                                @endcan
                                @can('usuarios.estado')
                                    <form action="{{ route('usuarios.estado', $usuario) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $usuario->estaActivo() ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                            {{ $usuario->estaActivo() ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
