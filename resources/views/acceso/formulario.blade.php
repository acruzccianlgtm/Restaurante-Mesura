@extends('layouts.app')

@php($esCliente = $tipo === 'cliente')

@section('titulo', $esCliente ? 'Acceso cliente' : 'Acceso empleado')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">{{ $esCliente ? 'Iniciar como cliente' : 'Iniciar como empleado' }}</h1>
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('acceso.ingresar', $tipo) }}" method="POST" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" name="correo" id="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo') }}" placeholder="correo@ejemplo.com">
                            @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Tu contraseña">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-restaurante">Ingresar</button>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
