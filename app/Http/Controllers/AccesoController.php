<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccesoController extends Controller
{
    private const ROLES_POR_TIPO = [
        'cliente' => ['cliente'],
        'empleado' => ['mesero', 'jefe de cocina', 'encargado', 'cocinero', 'cajero', 'encargado de categoria'],
    ];

    public function opciones(): View
    {
        return view('acceso.opciones');
    }

    public function formulario(string $tipo): View
    {
        return view('acceso.formulario', compact('tipo'));
    }

    public function ingresar(Request $request, string $tipo): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = User::query()->where('correo', $datos['correo'])->first();

        if ($usuario === null || ! Hash::check($datos['password'], $usuario->password)) {
            return redirect()
                ->route('acceso.formulario', $tipo)
                ->with('error', 'El correo o la contraseña son incorrectos.')
                ->withInput($request->only('correo'));
        }

        if (! $usuario->estaActivo()) {
            return redirect()
                ->route('acceso.formulario', $tipo)
                ->with('error', 'Tu usuario está desactivado. Solicita al administrador que lo reactive.')
                ->withInput($request->only('correo'));
        }

        if (! $usuario->hasRole(self::ROLES_POR_TIPO[$tipo])) {
            return redirect()
                ->route('acceso.formulario', $tipo)
                ->with('error', sprintf('Ese usuario no tiene acceso de %s.', $tipo))
                ->withInput($request->only('correo'));
        }

        Auth::login($usuario);

        return redirect()
            ->route('panel.index')
            ->with('exito', 'Bienvenido, '.$usuario->rol.'.');
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('exito', 'Cerraste sesión correctamente.');
    }
}
