@extends('layouts.app')

@section('titulo', 'Eliminar encargado')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-danger-subtle">
                <div class="card-body p-4">
                    <h1 class="h4 page-title text-danger mb-2">Eliminar encargado</h1>
                    <p class="text-muted">¿Está seguro de eliminar este encargado? Esta acción no se puede deshacer.</p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0"><strong>Nombre:</strong> {{ $encargado->nombre_completo }}</li>
                        <li class="list-group-item px-0"><strong>Cargo:</strong> {{ $encargado->cargo ?: 'Sin cargo' }}</li>
                        <li class="list-group-item px-0"><strong>Teléfono:</strong> {{ $encargado->telefono ?: 'Sin teléfono' }}</li>
                        <li class="list-group-item px-0">
                            <strong>Categorías a cargo:</strong>
                            @forelse ($encargado->categorias as $categoria)
                                {{ $categoria->nombre }}{{ $loop->last ? '' : ', ' }}
                            @empty
                                <span class="text-muted">Sin categoría asignada</span>
                            @endforelse
                        </li>
                    </ul>

                    @if ($encargado->categorias->isNotEmpty())
                        <div class="alert alert-warning">
                            Este encargado tiene categorías asignadas, por lo que no podrá eliminarse.
                        </div>
                    @endif

                    <form action="{{ route('encargados.destroy', $encargado) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger" @disabled($encargado->categorias->isNotEmpty())>Eliminar encargado</button>
                        <a href="{{ route('encargados.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
