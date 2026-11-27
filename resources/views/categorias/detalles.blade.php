@extends('layouts.app')

@section('titulo', 'Detalle de la categoría')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h1 class="h3 page-title mb-1">{{ $categoria->nombre }}</h1>
                            <p class="text-muted mb-0">Categoría #{{ $categoria->id_categoria }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @can('categorias.editar')
                                <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-restaurante">Editar</a>
                            @endcan
                            @can('categorias.eliminar')
                                <a href="{{ route('categorias.eliminar', $categoria) }}" class="btn btn-outline-danger">Eliminar</a>
                            @endcan
                            <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Descripción</dt>
                        <dd class="col-sm-9">{{ $categoria->descripcion ?: 'Sin descripción' }}</dd>

                        <dt class="col-sm-3">Encargado</dt>
                        <dd class="col-sm-9">{{ $categoria->encargado?->nombre_completo ?? 'Sin asignar' }}</dd>

                        <dt class="col-sm-3">Cargo</dt>
                        <dd class="col-sm-9">{{ $categoria->encargado?->cargo ?: 'Sin cargo' }}</dd>

                        <dt class="col-sm-3">Teléfono</dt>
                        <dd class="col-sm-9">{{ $categoria->encargado?->telefono ?: 'Sin teléfono' }}</dd>

                        <dt class="col-sm-3">Platillos</dt>
                        <dd class="col-sm-9">
                            <span class="badge text-bg-light border">{{ $categoria->platos_count }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
