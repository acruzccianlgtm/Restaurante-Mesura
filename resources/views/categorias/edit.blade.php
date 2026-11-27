@extends('layouts.app')

@section('titulo', 'Editar categoría')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Editar categoría</h1>
                <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('categorias.update', $categoria) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id_categoria" class="form-label">Código</label>
                            <input type="text" id="id_categoria" value="{{ $categoria->id_categoria }}" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $categoria->nombre) }}" maxlength="80" class="form-control @error('nombre') is-invalid @enderror" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" maxlength="255" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="id_encargado" class="form-label">Encargado</label>
                            <select name="id_encargado" id="id_encargado" class="form-select @error('id_encargado') is-invalid @enderror" required>
                                <option value="">Seleccione un encargado</option>
                                @foreach ($encargados as $encargado)
                                    <option value="{{ $encargado->id_encargado }}" @selected((int) old('id_encargado', $categoria->id_encargado) === $encargado->id_encargado)>
                                        {{ $encargado->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_encargado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-restaurante">Actualizar categoría</button>
                            <a href="{{ route('categorias.show', $categoria) }}" class="btn btn-outline-secondary">Ver detalles</a>
                            <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
