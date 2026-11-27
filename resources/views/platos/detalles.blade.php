@extends('layouts.app')

@section('titulo', 'Detalle del platillo')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h1 class="h3 page-title mb-1">{{ $plato->nombre }}</h1>
                            <p class="text-muted mb-0">Platillo #{{ $plato->id_plato }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @can('platos.editar')
                                @if (auth()->user()->puedeEditarPlato($plato))
                                    <a href="{{ route('platos.edit', $plato) }}" class="btn btn-restaurante">Editar</a>
                                @endif
                            @endcan
                            @can('platos.eliminar')
                                @if (auth()->user()->puedeEditarPlato($plato))
                                    <a href="{{ route('platos.eliminar', $plato) }}" class="btn btn-outline-danger">Eliminar</a>
                                @endif
                            @endcan
                            <a href="{{ route('platos.index') }}" class="btn btn-outline-secondary">Volver</a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-5">
                            @if ($plato->foto)
                                <img src="{{ asset($plato->foto) }}" alt="{{ $plato->nombre }}" class="foto-detalle">
                            @else
                                <div class="sin-foto" style="height: 260px;">Sin fotografía</div>
                            @endif
                        </div>

                        <div class="col-md-7">
                            <p class="mb-4">{{ $plato->descripcion ?: 'Sin descripción' }}</p>

                            <dl class="row mb-0">
                                <dt class="col-sm-4">Categoría</dt>
                                <dd class="col-sm-8">{{ $plato->categoria?->nombre ?? 'Sin categoría' }}</dd>

                                <dt class="col-sm-4">Encargado</dt>
                                <dd class="col-sm-8">{{ $plato->categoria?->encargado?->nombre_completo ?? 'Sin asignar' }}</dd>

                                <dt class="col-sm-4">Dificultad</dt>
                                <dd class="col-sm-8">{{ $plato->nivel_dificultad }} de 5</dd>

                                <dt class="col-sm-4">Precio final</dt>
                                <dd class="col-sm-8 fw-semibold">L {{ number_format((float) $plato->precio_final, 2) }}</dd>
                            </dl>
                        </div>
                    </div>

                    @can('recetas.ver')
                        <div class="border-top mt-4 pt-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Receta</h2>
                                    <div class="form-text">Ingredientes e instrucciones para la preparación.</div>
                                </div>
                                @can('recetas.editar')
                                    @if (auth()->user()->puedeEditarPlato($plato))
                                        <a href="{{ route('platos.receta.edit', $plato) }}" class="btn btn-sm btn-restaurante">Editar receta</a>
                                    @endif
                                @endcan
                            </div>

                            @if ($plato->recetas->isNotEmpty())
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Ingrediente</th>
                                                <th>Cantidad</th>
                                                <th>Unidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($plato->recetas as $receta)
                                                <tr>
                                                    <td>{{ $receta->ingrediente?->nombre ?? 'Ingrediente #'.$receta->id_ingrediente }}</td>
                                                    <td>{{ $receta->cantidad_requerida }}</td>
                                                    <td>{{ $receta->unidad_medida }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Este platillo aún no tiene ingredientes registrados en su receta.</p>
                            @endif

                            @php
                                $pasos = $plato->pasosReceta();
                                $lineas = $pasos === null ? [] : preg_split('/\r\n|\r|\n/', trim($pasos));
                            @endphp

                            <h3 class="h6">Pasos de preparación</h3>
                            @if ($lineas !== [] && $lineas !== false)
                                <ol>
                                    @foreach ($lineas as $linea)
                                        @if (trim($linea) !== '')
                                            <li>{{ preg_replace('/^\s*\d+[.)]\s*/', '', trim($linea)) }}</li>
                                        @endif
                                    @endforeach
                                </ol>
                            @else
                                <p class="text-muted">Esta receta aún no tiene pasos registrados.</p>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
