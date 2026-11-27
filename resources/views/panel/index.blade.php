@extends('layouts.app')

@section('titulo', 'Panel de control')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Panel de control</h1>
            <p class="text-muted mb-0">Bienvenido al espacio de trabajo de MESSURA.</p>
        </div>
        <span class="badge text-bg-light border text-capitalize">{{ auth()->user()->rol }}</span>
    </div>

    <div class="row g-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <p class="text-muted small text-uppercase fw-semibold mb-2">Platillos</p>
                    <p class="display-6 fw-bold mb-1">{{ $totalPlatos }}</p>
                    <p class="text-muted small mb-0">Registrados en la carta</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <p class="text-muted small text-uppercase fw-semibold mb-2">Categorías</p>
                    <p class="display-6 fw-bold mb-1">{{ $totalCategorias }}</p>
                    <p class="text-muted small mb-0">Secciones de la carta</p>
                </div>
            </div>
        </div>
        @can('empleados.ver')
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Encargados</p>
                        <p class="display-6 fw-bold mb-1">{{ $totalEncargados }}</p>
                        <p class="text-muted small mb-0">Responsables asignados</p>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    <div class="card mt-4">
        <div class="card-body p-4">
            <h2 class="h5 page-title mb-3">Accesos rápidos</h2>
            <div class="d-flex flex-wrap gap-2">
                @can('platos.ver')
                    <a href="{{ route('platos.index') }}" class="btn btn-restaurante">Ver platillos</a>
                @endcan
                @can('categorias.ver')
                    @unless (auth()->user()->esCliente())
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-restaurante">Ver categorías</a>
                    @endunless
                @endcan
                @can('usuarios.ver')
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-restaurante">Ver usuarios</a>
                @endcan
                @can('empleados.ver')
                    <a href="{{ route('encargados.index') }}" class="btn btn-outline-restaurante">Ver encargados</a>
                @endcan
                @can('inventario.ver')
                    <a href="{{ route('inventario.index') }}" class="btn btn-outline-restaurante">Ver inventario</a>
                @endcan
                @can('mesas.ver')
                    <a href="{{ route('mesas.index') }}" class="btn btn-outline-restaurante">Ver mesas</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
