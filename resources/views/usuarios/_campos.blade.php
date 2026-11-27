<h2 class="h5 mb-3">Datos personales</h2>

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario?->nombre ?? '') }}" maxlength="80" class="form-control @error('nombre') is-invalid @enderror" required>
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="apellido" class="form-label">Apellido</label>
        <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario?->apellido ?? '') }}" maxlength="80" class="form-control @error('apellido') is-invalid @enderror" required>
        @error('apellido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="edad" class="form-label">Edad</label>
        <input type="number" name="edad" id="edad" value="{{ old('edad', $usuario?->edad ?? '') }}" min="16" max="100" class="form-control @error('edad') is-invalid @enderror">
        @error('edad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario?->telefono ?? '') }}" maxlength="20" class="form-control @error('telefono') is-invalid @enderror">
        @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="ciudad" class="form-label">Ciudad</label>
        <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $usuario?->ciudad ?? '') }}" maxlength="40" class="form-control @error('ciudad') is-invalid @enderror">
        @error('ciudad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<hr class="my-4">

<h2 class="h5 mb-3">Acceso</h2>

<div class="row g-3">
    <div class="col-md-6">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" name="correo" id="correo" value="{{ old('correo', $usuario?->correo ?? '') }}" maxlength="150" class="form-control @error('correo') is-invalid @enderror" required>
        @error('correo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="8" @required($usuario === null)>
        @if ($usuario !== null)
            <div class="form-text">Déjala en blanco para conservar la contraseña actual.</div>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="rol" class="form-label">Rol</label>
        <select name="rol" id="rol" class="form-select @error('rol') is-invalid @enderror" required>
            @if ($usuario === null)
                <option value="">Seleccione un rol</option>
            @endif
            @foreach ($roles as $rol)
                <option value="{{ $rol }}" @selected(old('rol', $usuario?->rol ?? '') === $rol)>{{ ucfirst($rol) }}</option>
            @endforeach
        </select>
        @error('rol')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="id_categoria" class="form-label">Categoría asignada <span class="text-muted small">(solo para encargado de categoría)</span></label>
        <select name="id_categoria" id="id_categoria" class="form-select @error('id_categoria') is-invalid @enderror">
            <option value="">Seleccione una categoría</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id_categoria }}" @selected((int) old('id_categoria', $usuario?->id_categoria ?? 0) === $categoria->id_categoria)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_categoria')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
