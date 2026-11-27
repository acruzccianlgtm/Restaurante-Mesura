@extends('layouts.app')

@section('titulo', 'Nuevo usuario')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Nuevo usuario</h1>
                <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf

                        @include('usuarios._campos', [
                            'usuario' => null,
                            'roles' => $roles,
                            'categorias' => $categorias,
                        ])

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-restaurante">Guardar usuario</button>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
