@extends('layouts.app')

@section('titulo', 'Detalle del usuario')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h1 class="h3 page-title mb-1">{{ $usuario->nombre_completo }}</h1>
                            <p class="text-muted mb-0">Usuario #{{ $usuario->id }}</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @can('usuarios.editar')
                                @if ($usuario->estaActivo())
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-restaurante">Editar</a>
                                @else
                                    <button type="button" class="btn btn-restaurante" disabled title="Reactiva el usuario para poder editarlo">Editar</button>
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
                            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Nombre</dt>
                        <dd class="col-sm-9">{{ $usuario->nombre ?: 'Sin nombre' }}</dd>

                        <dt class="col-sm-3">Apellido</dt>
                        <dd class="col-sm-9">{{ $usuario->apellido ?: 'Sin apellido' }}</dd>

                        <dt class="col-sm-3">Edad</dt>
                        <dd class="col-sm-9">{{ $usuario->edad ?? 'Sin edad' }}</dd>

                        <dt class="col-sm-3">Teléfono</dt>
                        <dd class="col-sm-9">{{ $usuario->telefono ?: 'Sin teléfono' }}</dd>

                        <dt class="col-sm-3">Ciudad</dt>
                        <dd class="col-sm-9">{{ $usuario->ciudad ?: 'Sin ciudad' }}</dd>

                        <dt class="col-sm-3">Correo</dt>
                        <dd class="col-sm-9">{{ $usuario->correo }}</dd>

                        <dt class="col-sm-3">Rol</dt>
                        <dd class="col-sm-9 text-capitalize">{{ $usuario->rol }}</dd>

                        <dt class="col-sm-3">Categoría asignada</dt>
                        <dd class="col-sm-9">{{ $usuario->categoriaAsignada?->nombre ?? 'Sin categoría' }}</dd>

                        <dt class="col-sm-3">Estado</dt>
                        <dd class="col-sm-9">
                            <span class="badge {{ $usuario->estaActivo() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $usuario->estaActivo() ? 'Activo' : 'Inactivo' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
