@extends('layouts.app')

@section('titulo', 'Mi pedido')

@section('contenido')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 page-title mb-1">Mi pedido</h1>
            <p class="text-muted mb-0">Revisa los platillos que has agregado y el total a pagar.</p>
        </div>
        <a href="{{ route('platos.index') }}" class="btn btn-outline-restaurante">Seguir agregando platillos</a>
    </div>

    @if (empty($items))
        <div class="card">
            <div class="card-body py-5 text-center">
                <p class="mb-3 text-muted">Aún no has agregado platillos a tu pedido.</p>
                <a href="{{ route('platos.index') }}" class="btn btn-restaurante">Ver la carta</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Platillo</th>
                                    <th>Precio unitario</th>
                                    <th>Cantidad</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['plato']->nombre }}</div>
                                            <div class="small text-muted">{{ $item['plato']->categoria?->nombre ?? 'Sin categoría' }}</div>
                                        </td>
                                        <td>L {{ number_format((float) $item['plato']->precio_final, 2) }}</td>
                                        <td>
                                            <form action="{{ route('carrito.actualizar', $item['plato']) }}" method="POST" class="d-flex align-items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="cantidad" value="{{ $item['cantidad'] }}" inputmode="numeric" class="form-control form-control-sm" style="width: 78px;" aria-label="Cantidad de {{ $item['plato']->nombre }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">Actualizar</button>
                                            </form>
                                        </td>
                                        <td class="text-end fw-semibold">L {{ number_format($item['importe'], 2) }}</td>
                                        <td class="text-end text-nowrap">
                                            <form action="{{ route('carrito.quitar', $item['plato']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 page-title mb-3">Tipo de entrega</h2>
                        <form action="{{ route('carrito.tipo-servicio') }}" method="POST">
                            @csrf
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_servicio" id="tipo_recoger" value="recoger" {{ $tipoServicio === 'recoger' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo_recoger">Recoger en el restaurante</label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="tipo_servicio" id="tipo_domicilio" value="domicilio" {{ $tipoServicio === 'domicilio' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo_domicilio">A domicilio <span class="text-muted">(+L 40.00)</span></label>
                            </div>
                            <button type="submit" class="btn btn-outline-restaurante w-100 mt-3">Aplicar entrega</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 page-title mb-3">Resumen</h2>
                        <dl class="row mb-0">
                            <dt class="col-8 text-muted fw-normal">Subtotal</dt>
                            <dd class="col-4 text-end fw-semibold">L {{ number_format($subtotal, 2) }}</dd>
                            <dt class="col-8 text-muted fw-normal">Impuesto (15%)</dt>
                            <dd class="col-4 text-end fw-semibold">L {{ number_format($impuesto, 2) }}</dd>
                            @if ($envio > 0)
                                <dt class="col-8 text-muted fw-normal">Servicio a domicilio</dt>
                                <dd class="col-4 text-end fw-semibold">L {{ number_format($envio, 2) }}</dd>
                            @endif
                        </dl>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Total a pagar</span>
                            <span class="h4 mb-0 fw-bold" style="color: var(--restaurante);">L {{ number_format($total, 2) }}</span>
                        </div>
                        <form action="{{ route('carrito.vaciar') }}" method="POST" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">Vaciar pedido</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
