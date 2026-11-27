@extends('layouts.app')

@section('titulo', 'Inventario')

@section('contenido')
    @php
        $estilos = [
            'Normal' => 'text-bg-success',
            'Bajo' => 'text-bg-warning',
            'Agotado' => 'text-bg-danger',
            'Inactivo' => 'text-bg-secondary',
        ];
    @endphp

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <p class="small text-uppercase text-muted fw-semibold mb-1" style="letter-spacing: .14em;">Control de almacén</p>
            <h1 class="h3 page-title mb-0">Inventario actual</h1>
        </div>
        @can('inventario.crear')
            <button type="button" class="btn btn-restaurante" data-bs-toggle="modal" data-bs-target="#modal-crear">
                + Nuevo ingrediente
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ingrediente</th>
                        <th>Cantidad actual</th>
                        <th>Unidad</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ingredientes as $ingrediente)
                        <tr>
                            <td class="fw-semibold">{{ $ingrediente->nombre }}</td>
                            <td>
                                <div>{{ number_format((float) $ingrediente->cantidad_actual_almacen, 2) }}</div>
                                <div class="small text-muted">Mín. {{ number_format((float) $ingrediente->cantidad_minima, 2) }}</div>
                            </td>
                            <td>{{ $ingrediente->unidad_medida_almacen }}</td>
                            <td>
                                <span class="badge {{ $estilos[$ingrediente->nivel] }}">{{ $ingrediente->nivel }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                @can('inventario.editar')
                                    @if ($ingrediente->estaActivo())
                                        <button type="button" class="btn btn-sm btn-outline-restaurante" data-bs-toggle="modal" data-bs-target="#modal-editar-{{ $ingrediente->id_ingrediente }}">
                                            Editar
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva el ingrediente para poder editarlo">Editar</button>
                                    @endif
                                @endcan
                                @can('inventario.cantidad')
                                    @cannot('inventario.editar')
                                        @if ($ingrediente->estaActivo())
                                            <button type="button" class="btn btn-sm btn-outline-restaurante" data-bs-toggle="modal" data-bs-target="#modal-cantidad-{{ $ingrediente->id_ingrediente }}">
                                                Editar cantidad
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva el ingrediente para poder editar su cantidad">Editar cantidad</button>
                                        @endif
                                    @endcannot
                                @endcan
                                @can('inventario.estado')
                                    <form action="{{ route('inventario.estado', $ingrediente) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $ingrediente->estaActivo() ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                            {{ $ingrediente->estaActivo() ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-muted">No hay ingredientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('inventario.crear')
        <div class="modal fade" id="modal-crear" tabindex="-1" aria-labelledby="modal-crear-titulo" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('inventario.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="modal" value="crear">
                        <div class="modal-header">
                            <h2 class="h5 modal-title" id="modal-crear-titulo">Nuevo ingrediente</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            @include('inventario._formulario', ['ingrediente' => null, 'prefijo' => 'crear'])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-restaurante">Guardar ingrediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @foreach ($ingredientes as $ingrediente)
        @continue(! $ingrediente->estaActivo())
        @can('inventario.editar')
            <div class="modal fade" id="modal-editar-{{ $ingrediente->id_ingrediente }}" tabindex="-1" aria-labelledby="modal-editar-{{ $ingrediente->id_ingrediente }}-titulo" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('inventario.update', $ingrediente) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="modal" value="editar-{{ $ingrediente->id_ingrediente }}">
                            <div class="modal-header">
                                <h2 class="h5 modal-title" id="modal-editar-{{ $ingrediente->id_ingrediente }}-titulo">Editar {{ $ingrediente->nombre }}</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @include('inventario._formulario', ['ingrediente' => $ingrediente, 'prefijo' => 'editar-'.$ingrediente->id_ingrediente])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-restaurante">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endforeach

    @foreach ($ingredientes as $ingrediente)
        @continue(! $ingrediente->estaActivo())
        @can('inventario.cantidad')
            @cannot('inventario.editar')
                <div class="modal fade" id="modal-cantidad-{{ $ingrediente->id_ingrediente }}" tabindex="-1" aria-labelledby="modal-cantidad-{{ $ingrediente->id_ingrediente }}-titulo" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('inventario.cantidad', $ingrediente) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="modal" value="cantidad-{{ $ingrediente->id_ingrediente }}">
                                <div class="modal-header">
                                    <h2 class="h5 modal-title" id="modal-cantidad-{{ $ingrediente->id_ingrediente }}-titulo">Cantidad de {{ $ingrediente->nombre }}</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <label for="cantidad-actual-{{ $ingrediente->id_ingrediente }}" class="form-label">Cantidad actual</label>
                                    <input type="number" step="0.01" min="0" name="cantidad_actual_almacen" id="cantidad-actual-{{ $ingrediente->id_ingrediente }}" value="{{ old('cantidad_actual_almacen', $ingrediente->cantidad_actual_almacen) }}" class="form-control @error('cantidad_actual_almacen') is-invalid @enderror" required>
                                    @error('cantidad_actual_almacen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <p class="small text-muted mt-2 mb-0">Unidad: {{ $ingrediente->unidad_medida_almacen }} · Mínimo: {{ number_format((float) $ingrediente->cantidad_minima, 2) }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-restaurante">Guardar cantidad</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcannot
        @endcan
    @endforeach

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const objetivo = document.getElementById(@json('modal-'.old('modal', 'crear')));

                if (objetivo) {
                    bootstrap.Modal.getOrCreateInstance(objetivo).show();
                }
            });
        </script>
    @endif
@endsection
