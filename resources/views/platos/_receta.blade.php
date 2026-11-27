<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="h5 mb-1">Receta</h2>
                <div class="form-text">Ingredientes e instrucciones de preparación del platillo.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="agregar-ingrediente">Agregar ingrediente</button>
        </div>
    </div>

    <div class="col-12" id="filas-receta">
        @foreach ($filas as $indice => $fila)
            <div class="row g-2 align-items-end mb-2 fila-receta">
                <div class="col-md-5">
                    <label for="ingrediente-{{ $indice }}" class="form-label">Ingrediente</label>
                    <select name="receta[{{ $indice }}][id_ingrediente]" id="ingrediente-{{ $indice }}" class="form-select @error('receta.'.$indice.'.id_ingrediente') is-invalid @enderror" required>
                        <option value="">Seleccione un ingrediente</option>
                        @foreach ($ingredientes as $ingrediente)
                            <option value="{{ $ingrediente->id_ingrediente }}" @selected((int) ($fila['id_ingrediente'] ?? 0) === $ingrediente->id_ingrediente)>
                                {{ $ingrediente->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('receta.'.$indice.'.id_ingrediente')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="cantidad-{{ $indice }}" class="form-label">Cantidad</label>
                    <input type="number" name="receta[{{ $indice }}][cantidad_requerida]" id="cantidad-{{ $indice }}" value="{{ old('receta.'.$indice.'.cantidad_requerida', $fila['cantidad_requerida'] ?? '') }}" min="0.001" step="0.001" class="form-control @error('receta.'.$indice.'.cantidad_requerida') is-invalid @enderror" required>
                    @error('receta.'.$indice.'.cantidad_requerida')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="unidad-{{ $indice }}" class="form-label">Unidad</label>
                    <input type="text" name="receta[{{ $indice }}][unidad_medida]" id="unidad-{{ $indice }}" value="{{ old('receta.'.$indice.'.unidad_medida', $fila['unidad_medida'] ?? '') }}" maxlength="30" class="form-control @error('receta.'.$indice.'.unidad_medida') is-invalid @enderror" required>
                    @error('receta.'.$indice.'.unidad_medida')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-danger btn-sm quitar-ingrediente">Quitar</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="col-12">
        <label for="pasos" class="form-label">Pasos de preparación</label>
        <textarea name="pasos" id="pasos" rows="6" placeholder="Escribe un paso por línea" class="form-control @error('pasos') is-invalid @enderror" required>{{ old('pasos', $pasos) }}</textarea>
        <div class="form-text">Escribe cada paso en una línea diferente para que el cocinero pueda seguirlos.</div>
        @error('pasos')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<template id="plantilla-fila-receta">
    <div class="row g-2 align-items-end mb-2 fila-receta">
        <div class="col-md-5">
            <label for="ingrediente-__INDICE__" class="form-label">Ingrediente</label>
            <select name="receta[__INDICE__][id_ingrediente]" id="ingrediente-__INDICE__" class="form-select" required>
                <option value="">Seleccione un ingrediente</option>
                @foreach ($ingredientes as $ingrediente)
                    <option value="{{ $ingrediente->id_ingrediente }}">{{ $ingrediente->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="cantidad-__INDICE__" class="form-label">Cantidad</label>
            <input type="number" name="receta[__INDICE__][cantidad_requerida]" id="cantidad-__INDICE__" min="0.001" step="0.001" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label for="unidad-__INDICE__" class="form-label">Unidad</label>
            <input type="text" name="receta[__INDICE__][unidad_medida]" id="unidad-__INDICE__" maxlength="30" class="form-control" required>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-danger btn-sm quitar-ingrediente">Quitar</button>
        </div>
    </div>
</template>

<script>
    (function () {
        const contenedor = document.getElementById('filas-receta');
        const plantilla = document.getElementById('plantilla-fila-receta');
        const agregar = document.getElementById('agregar-ingrediente');
        let indice = {{ count($filas) }};

        function actualizarBotones() {
            const filas = contenedor.querySelectorAll('.fila-receta');
            filas.forEach(function (fila) {
                fila.querySelector('.quitar-ingrediente').disabled = filas.length === 1;
            });
        }

        agregar.addEventListener('click', function () {
            indice += 1;
            const html = plantilla.innerHTML.replaceAll('__INDICE__', indice);
            contenedor.insertAdjacentHTML('beforeend', html);
            actualizarBotones();
        });

        contenedor.addEventListener('click', function (evento) {
            if (! evento.target.classList.contains('quitar-ingrediente')) {
                return;
            }

            evento.target.closest('.fila-receta').remove();
            actualizarBotones();
        });

        actualizarBotones();
    })();
</script>
