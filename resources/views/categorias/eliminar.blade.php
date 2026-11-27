@extends('layouts.app')

@section('titulo', 'Eliminar categoría')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-danger-subtle">
                <div class="card-body p-4">
                    <h1 class="h4 page-title text-danger mb-2">Eliminar categoría</h1>
                    <p class="text-muted">¿Está seguro de eliminar esta categoría? Esta acción no se puede deshacer.</p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0"><strong>Nombre:</strong> {{ $categoria->nombre }}</li>
                        <li class="list-group-item px-0"><strong>Descripción:</strong> {{ $categoria->descripcion ?: 'Sin descripción' }}</li>
                        <li class="list-group-item px-0"><strong>Encargado:</strong> {{ $categoria->encargado?->nombre_completo ?? 'Sin asignar' }}</li>
                        <li class="list-group-item px-0"><strong>Platillos:</strong> {{ $categoria->platos_count }}</li>
                    </ul>

                    @if ($categoria->platos_count > 0)
                        <div class="alert alert-warning">
                            Esta categoría tiene platillos registrados, por lo que no podrá eliminarse.
                        </div>
                    @endif

                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger" @disabled($categoria->platos_count > 0)>Eliminar categoría</button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
