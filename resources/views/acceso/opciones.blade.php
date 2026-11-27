@extends('layouts.app')

@section('titulo', 'Acceso')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h1 class="h3 page-title mb-2">¿Cómo deseas ingresar?</h1>
                <p class="text-muted mb-0">Selecciona el tipo de acceso para continuar.</p>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h2 class="h5 page-title">Cliente</h2>
                            <p class="text-muted">Ingresa para consultar la carta y realizar pedidos.</p>
                            <a href="{{ route('acceso.formulario', 'cliente') }}" class="btn btn-restaurante mt-auto">Iniciar como cliente</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4 text-center d-flex flex-column">
                            <h2 class="h5 page-title">Empleado</h2>
                            <p class="text-muted">Ingresa para administrar la operación del restaurante.</p>
                            <a href="{{ route('acceso.formulario', 'empleado') }}" class="btn btn-outline-restaurante mt-auto">Iniciar como empleado</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
