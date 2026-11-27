@extends('layouts.app')

@section('titulo', 'Detalle del encargado')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h1 class="h3 page-title mb-1">{{ $encargado->nombre_completo }}</h1>
                            <p class="text-muted mb-0">Encargado #{{ $encargado->id_encargado }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @can('empleados.editar')
                                <a href="{{ route('encargados.edit', $encargado) }}" class="btn btn-restaurante">Editar</a>
                            @endcan
                            @can('empleados.eliminar')
                                <a href="{{ route('encargados.eliminar', $encargado) }}" class="btn btn-outline-danger">Eliminar</a>
                            @endcan
                            <a href="{{ route('encargados.index') }}" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Nombre</dt>
                        <dd class="col-sm-9">{{ $encargado->nombre }}</dd>

                        <dt class="col-sm-3">Apellido</dt>
                        <dd class="col-sm-9">{{ $encargado->apellido }}</dd>

                        <dt class="col-sm-3">Edad</dt>
                        <dd class="col-sm-9">{{ $encargado->edad ?? 'Sin edad' }}</dd>

                        <dt class="col-sm-3">Cargo</dt>
                        <dd class="col-sm-9">{{ $encargado->cargo ?: 'Sin cargo' }}</dd>

                        <dt class="col-sm-3">Teléfono</dt>
                        <dd class="col-sm-9">{{ $encargado->telefono ?: 'Sin teléfono' }}</dd>

                        <dt class="col-sm-3">Ciudad</dt>
                        <dd class="col-sm-9">{{ $encargado->ciudad ?: 'Sin ciudad' }}</dd>

                        <dt class="col-sm-3">Categorías a cargo</dt>
                        <dd class="col-sm-9">
                            @forelse ($encargado->categorias as $categoria)
                                <span class="badge text-bg-light border">{{ $categoria->nombre }}</span>
                            @empty
                                <span class="text-muted">Sin categoría asignada</span>
                            @endforelse
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
