@extends('layouts.app')

@section('titulo', 'Carta')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Carta de platillos</h1>
            <p class="text-muted mb-0">{{ auth()->user()->can('platos.crear') ? 'Consulta y administra los platillos del restaurante.' : 'Consulta los platillos disponibles del restaurante.' }}</p>
        </div>
        @can('platos.crear')
            <a href="{{ route('platos.create') }}" class="btn btn-restaurante">Nuevo platillo</a>
        @endcan
    </div>

    @if ($platos->isEmpty())
        <div class="card">
            <div class="card-body py-5 text-center">
                <p class="mb-3 text-muted">No hay platillos registrados.</p>
                @can('platos.crear')
                    <a href="{{ route('platos.create') }}" class="btn btn-restaurante">Agregar el primer platillo</a>
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
                            <th>Foto</th>
                            <th>Platillo</th>
                            <th>Categoría</th>
                            <th>Dificultad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($platos as $plato)
                            @php($nivel = $plato->nivel_dificultad)
                            <tr>
                                <td class="text-muted">{{ $plato->id_plato }}</td>
                                <td>
                                    @if ($plato->foto)
                                        <img src="{{ asset($plato->foto) }}" alt="{{ $plato->nombre }}" class="foto-miniatura">
                                    @else
                                        <div class="sin-foto" style="width: 56px; height: 56px;">Sin foto</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $plato->nombre }}</div>
                                    <div class="small text-muted">{{ $plato->descripcion ?: 'Sin descripción' }}</div>
                                </td>
                                <td>{{ $plato->categoria?->nombre ?? 'Sin categoría' }}</td>
                                <td>
                                    <span class="badge {{ $nivel <= 2 ? 'text-bg-success' : ($nivel === 3 ? 'text-bg-warning' : 'text-bg-danger') }}">
                                        {{ $nivel }} de 5
                                    </span>
                                </td>
                            <td class="fw-semibold">L {{ number_format((float) $plato->precio_final, 2) }}</td>
                            <td>
                                <span class="badge {{ $plato->estaActivo() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $plato->estaActivo() ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('platos.show', $plato) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                @if (auth()->user()->esCliente() && $plato->estaActivo())
                                    <form action="{{ route('carrito.agregar', $plato) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-restaurante">Agregar al pedido</button>
                                    </form>
                                @endif
                                @can('platos.editar')
                                    @if (auth()->user()->puedeEditarPlato($plato))
                                        @if ($plato->estaActivo())
                                            <a href="{{ route('platos.edit', $plato) }}" class="btn btn-sm btn-outline-restaurante">Editar</a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Reactiva el platillo para poder editarlo">Editar</button>
                                        @endif
                                    @endif
                                @endcan
                                @can('platos.eliminar')
                                    @if (auth()->user()->puedeEditarPlato($plato))
                                        <a href="{{ route('platos.eliminar', $plato) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                                    @endif
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
