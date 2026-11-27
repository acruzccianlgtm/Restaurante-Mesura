@extends('layouts.app')

@section('titulo', 'Nueva mesa')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Nueva mesa</h1>
                <a href="{{ route('mesas.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('mesas.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="capacidad" class="form-label">Cantidad de personas por mesa</label>
                            <input type="number" name="capacidad" id="capacidad" value="{{ old('capacidad', 2) }}" min="1" max="30" class="form-control @error('capacidad') is-invalid @enderror" required>
                            @error('capacidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-restaurante">Guardar mesa</button>
                            <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
