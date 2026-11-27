@extends('layouts.app')

@section('titulo', 'Editar platillo')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Editar platillo</h1>
                <a href="{{ route('platos.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la carta</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('platos.update', $plato) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="nombre" class="form-label">Nombre del platillo</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $plato->nombre) }}" maxlength="120" class="form-control @error('nombre') is-invalid @enderror" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="nivel_dificultad" class="form-label">Dificultad</label>
                                <select name="nivel_dificultad" id="nivel_dificultad" class="form-select @error('nivel_dificultad') is-invalid @enderror" required>
                                    @for ($nivel = 1; $nivel <= 5; $nivel++)
                                        <option value="{{ $nivel }}" @selected((int) old('nivel_dificultad', $plato->nivel_dificultad) === $nivel)>{{ $nivel }} de 5</option>
                                    @endfor
                                </select>
                                @error('nivel_dificultad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $plato->descripcion) }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="precio_final" class="form-label">Precio final (L)</label>
                                <input type="number" name="precio_final" id="precio_final" value="{{ old('precio_final', $plato->precio_final) }}" min="0" step="0.01" class="form-control @error('precio_final') is-invalid @enderror" required>
                                @error('precio_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="id_categoria" class="form-label">Categoría</label>
                                <select name="id_categoria" id="id_categoria" class="form-select @error('id_categoria') is-invalid @enderror" required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}" @selected((int) old('id_categoria', $plato->id_categoria) === $categoria->id_categoria)>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                @if ($plato->foto)
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        <img src="{{ asset($plato->foto) }}" alt="{{ $plato->nombre }}" class="foto-miniatura">
                                        <a href="{{ asset($plato->foto) }}" target="_blank" class="small">Ver fotografía actual</a>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="eliminar_foto" id="eliminar_foto" value="1" class="form-check-input" @checked(old('eliminar_foto'))>
                                        <label for="eliminar_foto" class="form-check-label">Eliminar la fotografía actual</label>
                                    </div>
                                @endif

                                <label for="foto" class="form-label">Nueva fotografía (opcional)</label>
                                <input type="file" name="foto" id="foto" accept="image/png,image/jpeg,image/webp" class="form-control @error('foto') is-invalid @enderror">
                                <div class="form-text">Formatos JPG, PNG o WEBP. Tamaño máximo 2 MB.</div>
                                @error('foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-restaurante">Actualizar platillo</button>
                            @can('recetas.editar')
                                <a href="{{ route('platos.receta.edit', $plato) }}" class="btn btn-outline-secondary">Editar receta</a>
                            @endcan
                            <a href="{{ route('platos.show', $plato) }}" class="btn btn-outline-secondary">Ver detalles</a>
                            <a href="{{ route('platos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
