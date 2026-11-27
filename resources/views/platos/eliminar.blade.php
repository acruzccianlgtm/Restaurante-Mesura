@extends('layouts.app')

@section('titulo', 'Eliminar platillo')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-danger-subtle">
                <div class="card-body p-4">
                    <h1 class="h4 page-title text-danger mb-2">Eliminar platillo</h1>
                    <p class="text-muted">¿Está seguro de eliminar este platillo? Esta acción no se puede deshacer.</p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0"><strong>Nombre:</strong> {{ $plato->nombre }}</li>
                        <li class="list-group-item px-0"><strong>Descripción:</strong> {{ $plato->descripcion ?: 'Sin descripción' }}</li>
                        <li class="list-group-item px-0"><strong>Categoría:</strong> {{ $plato->categoria?->nombre ?? 'Sin categoría' }}</li>
                        <li class="list-group-item px-0"><strong>Dificultad:</strong> {{ $plato->nivel_dificultad }} de 5</li>
                        <li class="list-group-item px-0"><strong>Precio:</strong> L {{ number_format((float) $plato->precio_final, 2) }}</li>
                    </ul>

                    <form action="{{ route('platos.destroy', $plato) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">Eliminar platillo</button>
                        <a href="{{ route('platos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
