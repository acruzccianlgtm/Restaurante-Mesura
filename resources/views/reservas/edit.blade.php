@extends('layouts.app')

@section('titulo', 'Editar reserva')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 page-title mb-0">Editar reserva</h1>
                <a href="{{ route('mesas.index') }}" class="btn btn-sm btn-outline-secondary">Volver a la lista</a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('reservas.update', $reserva) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id_mesa" class="form-label">Mesa</label>
                            <select name="id_mesa" id="id_mesa" class="form-select @error('id_mesa') is-invalid @enderror" required>
                                @foreach ($mesas as $mesaOpcion)
                                    <option value="{{ $mesaOpcion->id_mesa }}" @selected((int) old('id_mesa', $reserva->id_mesa) === $mesaOpcion->id_mesa)>
                                        Mesa {{ $mesaOpcion->id_mesa }} · {{ $mesaOpcion->capacidad }} personas
                                    </option>
                                @endforeach
                            </select>
                            @error('id_mesa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="nombre_cliente" class="form-label">Nombre del cliente</label>
                                <input type="text" name="nombre_cliente" id="nombre_cliente" value="{{ old('nombre_cliente', $reserva->nombre_cliente) }}" maxlength="120" class="form-control @error('nombre_cliente') is-invalid @enderror" required>
                                @error('nombre_cliente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $reserva->telefono) }}" maxlength="20" class="form-control @error('telefono') is-invalid @enderror">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="fecha_reserva" class="form-label">Fecha</label>
                                <input type="date" name="fecha_reserva" id="fecha_reserva" value="{{ old('fecha_reserva', $reserva->fecha_reserva) }}" class="form-control @error('fecha_reserva') is-invalid @enderror" required>
                                @error('fecha_reserva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="hora_reserva" class="form-label">Hora</label>
                                <input type="time" name="hora_reserva" id="hora_reserva" value="{{ old('hora_reserva', $reserva->hora_reserva) }}" class="form-control @error('hora_reserva') is-invalid @enderror" required>
                                @error('hora_reserva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="cantidad_personas" class="form-label">Cantidad de personas</label>
                                <input type="number" name="cantidad_personas" id="cantidad_personas" value="{{ old('cantidad_personas', $reserva->cantidad_personas) }}" min="1" class="form-control @error('cantidad_personas') is-invalid @enderror" required>
                                @error('cantidad_personas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-restaurante">Actualizar reserva</button>
                            <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
