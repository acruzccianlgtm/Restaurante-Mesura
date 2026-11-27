<?php

namespace App\Http\Controllers;

use App\Models\Plato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    private const IMPUESTO = 0.15;

    private const COSTO_DOMICILIO = 40.0;

    public function index(): View
    {
        $carrito = $this->carrito();
        $tipoServicio = $this->tipoServicio();
        $envio = $tipoServicio === 'domicilio' ? self::COSTO_DOMICILIO : 0.0;

        $platos = Plato::query()
            ->whereIn('id_plato', array_keys($carrito))
            ->get()
            ->keyBy('id_plato');

        $items = [];
        $subtotal = 0.0;

        foreach ($carrito as $idPlato => $cantidad) {
            $plato = $platos->get($idPlato);

            if ($plato === null) {
                continue;
            }

            $importe = (float) $plato->precio_final * $cantidad;
            $items[] = [
                'plato' => $plato,
                'cantidad' => $cantidad,
                'importe' => $importe,
            ];
            $subtotal += $importe;
        }

        $impuesto = $subtotal * self::IMPUESTO;

        return view('carrito.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'envio' => $envio,
            'tipoServicio' => $tipoServicio,
            'total' => $subtotal + $impuesto + $envio,
        ]);
    }

    public function establecerTipoServicio(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_servicio' => ['required', 'in:domicilio,recoger'],
        ]);

        $tipo = (string) $datos['tipo_servicio'];
        session()->put($this->claveTipoServicio(), $tipo);

        return redirect()
            ->route('carrito.index')
            ->with('exito', $tipo === 'domicilio'
                ? 'Se aplicó el servicio a domicilio.'
                : 'Recogerás tu pedido en el restaurante.');
    }

    public function agregar(Request $request, Plato $plato): RedirectResponse
    {
        if (! $plato->estaActivo()) {
            return redirect()
                ->route('platos.index')
                ->with('error', 'Ese platillo no está disponible.');
        }

        $datos = $request->validate([
            'cantidad' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $cantidad = (int) ($datos['cantidad'] ?? 1);
        $idPlato = (int) $plato->getKey();
        $carrito = $this->carrito();
        $carrito[$idPlato] = min(99, ($carrito[$idPlato] ?? 0) + $cantidad);

        $this->guardar($carrito);

        return redirect()
            ->route('platos.index')
            ->with('exito', $plato->nombre.' se agregó a tu pedido.');
    }

    public function actualizar(Request $request, Plato $plato): RedirectResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cantidad = (int) $datos['cantidad'];
        $idPlato = (int) $plato->getKey();
        $carrito = $this->carrito();

        if ($cantidad === 0) {
            unset($carrito[$idPlato]);
        } else {
            $carrito[$idPlato] = $cantidad;
        }

        $this->guardar($carrito);

        return redirect()
            ->route('carrito.index')
            ->with('exito', 'La cantidad se actualizó correctamente.');
    }

    public function quitar(Plato $plato): RedirectResponse
    {
        $carrito = $this->carrito();
        unset($carrito[(int) $plato->getKey()]);

        $this->guardar($carrito);

        return redirect()
            ->route('carrito.index')
            ->with('exito', 'El platillo se quitó de tu pedido.');
    }

    public function vaciar(): RedirectResponse
    {
        session()->forget($this->clave());

        return redirect()
            ->route('carrito.index')
            ->with('exito', 'Tu pedido se vació correctamente.');
    }

    /** @return array<int, int> */
    private function carrito(): array
    {
        $carrito = session()->get($this->clave(), []);

        if (! is_array($carrito)) {
            return [];
        }

        $limpio = [];

        foreach ($carrito as $idPlato => $cantidad) {
            if (is_numeric($idPlato) && is_numeric($cantidad) && (int) $cantidad > 0) {
                $limpio[(int) $idPlato] = (int) $cantidad;
            }
        }

        return $limpio;
    }

    /** @param array<int, int> $carrito */
    private function guardar(array $carrito): void
    {
        session()->put($this->clave(), $carrito);
    }

    private function clave(): string
    {
        return 'carrito_'.(string) auth()->id();
    }

    private function claveTipoServicio(): string
    {
        return 'tipo_servicio_'.(string) auth()->id();
    }

    private function tipoServicio(): string
    {
        $tipo = session()->get($this->claveTipoServicio(), 'recoger');

        if (is_string($tipo) && in_array($tipo, ['domicilio', 'recoger'], true)) {
            return $tipo;
        }

        return 'recoger';
    }
}
