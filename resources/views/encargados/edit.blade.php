@extends('layouts.app')

@section('titulo', 'Editar encargado')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Editar encargado</h1>
                <a href="{{ route('encargados.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('encargados.update', $encargado) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id_encargado" class="form-label">Código</label>
                            <input type="text" id="id_encargado" value="{{ $encargado->id_encargado }}" class="form-control" disabled>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $encargado->nombre) }}" maxlength="80" class="form-control @error('nombre') is-invalid @enderror" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $encargado->apellido) }}" maxlength="80" class="form-control @error('apellido') is-invalid @enderror" required>
                                @error('apellido')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="edad" class="form-label">Edad</label>
                                <input type="number" name="edad" id="edad" value="{{ old('edad', $encargado->edad) }}" min="16" max="100" class="form-control @error('edad') is-invalid @enderror">
                                @error('edad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $encargado->telefono) }}" maxlength="20" class="form-control @error('telefono') is-invalid @enderror">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $encargado->ciudad) }}" maxlength="40" class="form-control @error('ciudad') is-invalid @enderror">
                                @error('ciudad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="hidden" name="cargo" value="{{ old('cargo', $encargado->cargo ?: 'Encargado de categoria') }}">
                                <input type="text" id="cargo" value="Encargado de categoria" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-restaurante">Actualizar encargado</button>
                            <a href="{{ route('encargados.show', $encargado) }}" class="btn btn-outline-secondary">Ver detalles</a>
                            <a href="{{ route('encargados.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
