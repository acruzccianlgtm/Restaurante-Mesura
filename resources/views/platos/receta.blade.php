@extends('layouts.app')

@section('titulo', 'Editar receta')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <div>
                    <h1 class="h3 page-title mb-1">Editar receta</h1>
                    <p class="text-muted mb-0">{{ $plato->nombre }}</p>
                </div>
                <a href="{{ route('platos.show', $plato) }}" class="btn btn-sm btn-outline-secondary">Volver al platillo</a>
            </div>

            @if ($ingredientes->isEmpty())
                <div class="alert alert-warning">
                    No hay ingredientes activos.
                    <a href="{{ route('inventario.index') }}" class="alert-link">Registre ingredientes</a>
                    antes de editar la receta.
                </div>
            @endif

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('platos.receta.update', $plato) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @php
                            $filas = old('receta', $plato->recetas->map(fn ($receta) => [
                                'id_ingrediente' => $receta->id_ingrediente,
                                'cantidad_requerida' => $receta->cantidad_requerida,
                                'unidad_medida' => $receta->unidad_medida,
                            ])->all());

                            if ($filas === []) {
                                $filas = [['id_ingrediente' => '', 'cantidad_requerida' => '', 'unidad_medida' => '']];
                            }
                        @endphp

                        @include('platos._receta', [
                            'ingredientes' => $ingredientes,
                            'filas' => $filas,
                            'pasos' => old('pasos', $plato->pasosReceta()),
                        ])

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-restaurante" @disabled($ingredientes->isEmpty())>Guardar receta</button>
                            <a href="{{ route('platos.show', $plato) }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
