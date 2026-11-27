@php($prefijo = $prefijo ?? 'ingrediente')

<div class="mb-3">
    <label for="{{ $prefijo }}-nombre" class="form-label">Ingrediente</label>
    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="{{ $prefijo }}-nombre" name="nombre" value="{{ old('nombre', $ingrediente?->nombre) }}" required>
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row g-3">
    <div class="col-sm-6">
        <label for="{{ $prefijo }}-cantidad" class="form-label">Cantidad actual</label>
        <input type="number" step="0.01" min="0" class="form-control @error('cantidad_actual_almacen') is-invalid @enderror" id="{{ $prefijo }}-cantidad" name="cantidad_actual_almacen" value="{{ old('cantidad_actual_almacen', $ingrediente?->cantidad_actual_almacen) }}" required>
        @error('cantidad_actual_almacen')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-sm-6">
        <label for="{{ $prefijo }}-unidad" class="form-label">Unidad de medida</label>
        <input type="text" class="form-control @error('unidad_medida_almacen') is-invalid @enderror" id="{{ $prefijo }}-unidad" name="unidad_medida_almacen" value="{{ old('unidad_medida_almacen', $ingrediente?->unidad_medida_almacen) }}" placeholder="kg, L, pza..." required>
        @error('unidad_medida_almacen')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-3">
    <label for="{{ $prefijo }}-minima" class="form-label">Cantidad mínima</label>
    <input type="number" step="0.01" min="0" class="form-control @error('cantidad_minima') is-invalid @enderror" id="{{ $prefijo }}-minima" name="cantidad_minima" value="{{ old('cantidad_minima', $ingrediente?->cantidad_minima) }}" required>
    <div class="form-text">Cuando la cantidad actual sea menor o igual a este valor, el estado se mostrará como "Bajo".</div>
    @error('cantidad_minima')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
