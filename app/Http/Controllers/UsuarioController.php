<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    private const ROLES_CREACION = ['mesero', 'cajero', 'cocinero', 'encargado de categoria'];

    public function index(): View
    {
        $usuarios = User::query()->orderBy('correo')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('usuarios.create', [
            'roles' => self::ROLES_CREACION,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')],
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'edad' => ['nullable', 'integer', 'min:16', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'ciudad' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', Rule::in(self::ROLES_CREACION)],
            'id_categoria' => [
                Rule::requiredIf($request->input('rol') === 'encargado de categoria'),
                'nullable',
                'integer',
                'exists:categoria,id_categoria',
            ],
        ], $this->mensajesValidacion());

        if ($datos['rol'] !== 'encargado de categoria') {
            $datos['id_categoria'] = null;
        }

        User::create($datos + ['estado' => 'activo']);

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'El usuario se creó correctamente.');
    }

    public function show(User $usuario): View
    {
        return view('usuarios.detalles', compact('usuario'));
    }

    public function edit(User $usuario): View|RedirectResponse
    {
        $redireccion = $this->verificarActivo($usuario);

        if ($redireccion !== null) {
            return $redireccion;
        }

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->rolesDisponibles(),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $redireccion = $this->verificarActivo($usuario);

        if ($redireccion !== null) {
            return $redireccion;
        }

        $datos = $request->validate([
            'correo' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo')->ignore($usuario->id),
            ],
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'edad' => ['nullable', 'integer', 'min:16', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'ciudad' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8'],
            'rol' => ['required', Rule::in($this->rolesDisponibles())],
            'id_categoria' => [
                Rule::requiredIf($request->input('rol') === 'encargado de categoria'),
                'nullable',
                'integer',
                'exists:categoria,id_categoria',
            ],
        ], $this->mensajesValidacion());

        if ($datos['rol'] !== 'encargado de categoria') {
            $datos['id_categoria'] = null;
        }

        if (array_key_exists('password', $datos) && blank($datos['password'])) {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'El usuario se actualizó correctamente.');
    }

    public function cambiarEstado(User $usuario): RedirectResponse
    {
        if ($usuario->is(auth()->user())) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->estado = $usuario->estaActivo() ? 'inactivo' : 'activo';
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('exito', $usuario->estaActivo() ? 'Usuario reactivado.' : 'Usuario desactivado.');
    }

    private function verificarActivo(User $usuario): ?RedirectResponse
    {
        if ($usuario->estaActivo()) {
            return null;
        }

        return redirect()
            ->route('usuarios.index')
            ->with('error', 'Reactiva el usuario antes de editarlo.');
    }

    /** @return array<string, string> */
    private function mensajesValidacion(): array
    {
        return [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.unique' => 'Ya existe un usuario con ese correo.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.min' => 'La edad debe ser al menos 16 años.',
            'edad.max' => 'La edad no puede ser mayor a 100 años.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'ciudad.max' => 'La ciudad no puede tener más de 40 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'id_categoria.required' => 'Selecciona la categoría asignada.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
        ];
    }

    /** @return array<int, string> */
    private function rolesDisponibles(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'encargado')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
