@extends('layouts.app')

@section('titulo', 'Categorías')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Categorías</h1>
            <p class="text-muted mb-0">Organiza la carta y asigna un encargado a cada categoría.</p>
        </div>
        @can('categorias.crear')
            <a href="{{ route('categorias.create') }}" class="btn btn-restaurante">Nueva categoría</a>
        @endcan
    </div>

    @if ($categorias->isEmpty())
        <div class="card">
            <div class="card-body py-5 text-center">
                <p class="mb-3 text-muted">No hay categorías registradas.</p>
                @can('categorias.crear')
                    <a href="{{ route('categorias.create') }}" class="btn btn-restaurante">Crear la primera categoría</a>
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
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Encargado</th>
                            <th class="text-center">Platillos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            <tr>
                                <td class="text-muted">{{ $categoria->id_categoria }}</td>
                                <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                <td class="text-muted">{{ $categoria->descripcion ?: 'Sin descripción' }}</td>
                                <td>{{ $categoria->encargado?->nombre_completo ?? 'Sin asignar' }}</td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border">{{ $categoria->platos_count }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $categoria->estaActivo() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $categoria->estaActivo() ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('categorias.show', $categoria) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    @can('categorias.editar')
                                        @if ($categoria->estaActivo())
                                            <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-sm btn-outline-restaurante">Editar</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva la categoría para poder editarla">Editar</button>
                                        @endif
                                    @endcan
                                    @can('categorias.eliminar')
                                        <a href="{{ route('categorias.eliminar', $categoria) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
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
