@extends('layouts.app')

@section('titulo', 'Nueva categoría')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Nueva categoría</h1>
                <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            @if ($encargados->isEmpty())
                <div class="alert alert-warning">
                    No hay encargados registrados.
                    <a href="{{ route('encargados.create') }}" class="alert-link">Cree un encargado</a>
                    antes de crear una categoría.
                </div>
            @endif

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('categorias.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" maxlength="80" class="form-control @error('nombre') is-invalid @enderror" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" maxlength="255" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="id_encargado" class="form-label">Encargado</label>
                            <select name="id_encargado" id="id_encargado" class="form-select @error('id_encargado') is-invalid @enderror" required>
                                <option value="">Seleccione un encargado</option>
                                @foreach ($encargados as $encargado)
                                    <option value="{{ $encargado->id_encargado }}" @selected((int) old('id_encargado') === $encargado->id_encargado)>
                                        {{ $encargado->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_encargado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-restaurante" @disabled($encargados->isEmpty())>Guardar categoría</button>
                            <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
