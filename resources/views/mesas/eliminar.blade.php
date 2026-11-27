@extends('layouts.app')

@section('titulo', 'Eliminar mesa')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-danger-subtle">
                <div class="card-body p-4">
                    <h1 class="h4 page-title text-danger mb-2">Eliminar mesa</h1>
                    <p class="text-muted">¿Está seguro de eliminar esta mesa? Esta acción no se puede deshacer.</p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0"><strong>Mesa:</strong> {{ $mesa->id_mesa }}</li>
                        <li class="list-group-item px-0"><strong>Capacidad:</strong> {{ $mesa->capacidad }} personas</li>
                        <li class="list-group-item px-0"><strong>Estado:</strong> {{ ucfirst($mesa->estado) }}</li>
                        <li class="list-group-item px-0"><strong>Reservas:</strong> {{ $mesa->reservas_count }}</li>
                    </ul>

                    @if ($mesa->reservas_count > 0)
                        <div class="alert alert-warning">
                            Esta mesa tiene reservas registradas; también se eliminarán.
                        </div>
                    @endif

                    <form action="{{ route('mesas.destroy', $mesa) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">Eliminar mesa</button>
                        <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
