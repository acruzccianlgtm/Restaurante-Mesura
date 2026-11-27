<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $correo
 * @property string|null $nombre
 * @property string|null $apellido
 * @property int|null $edad
 * @property string|null $telefono
 * @property string|null $ciudad
 * @property string $password
 * @property string $rol
 * @property int|null $id_categoria
 * @property string $estado
 * @property-read Categoria|null $categoriaAsignada
 */
#[Fillable(['correo', 'nombre', 'apellido', 'edad', 'telefono', 'ciudad', 'password', 'rol', 'id_categoria', 'estado'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $table = 'usuarios';

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'edad' => 'integer',
            'id_categoria' => 'integer',
        ];
    }

    /**
     * Mantiene sincronizado el rol principal con los roles de Spatie.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if (blank($user->rol)) {
                return;
            }

            if (! Schema::hasTable(config('permission.table_names.roles'))) {
                return;
            }

            $role = Role::query()
                ->where('name', $user->rol)
                ->where('guard_name', 'web')
                ->first();

            if ($role !== null) {
                $user->syncRoles([$role]);
            }
        });
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /** @return BelongsTo<Categoria, $this> */
    public function categoriaAsignada(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function esEncargadoDeCategoria(): bool
    {
        return $this->rol === 'encargado de categoria';
    }

    public function puedeGestionarCategoria(int $idCategoria): bool
    {
        if (! $this->esEncargadoDeCategoria()) {
            return true;
        }

        return $this->id_categoria === $idCategoria;
    }

    public function puedeEditarPlato(Plato $plato): bool
    {
        return $this->puedeGestionarCategoria((int) $plato->id_categoria);
    }

    public function esCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    /** @return Attribute<string, string> */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(function (): string {
            $nombre = $this->getAttribute('nombre');
            $apellido = $this->getAttribute('apellido');

            if (filled($nombre) || filled($apellido)) {
                return trim($nombre.' '.$apellido);
            }

            $partes = preg_split('/[._\-0-9]+/', Str::before($this->correo, '@'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $nombre = Str::ucfirst(Str::lower($partes[0] ?? ''));
            $apellido = Str::ucfirst(Str::lower($partes[1] ?? ''));

            return trim($nombre.' '.$apellido) ?: $this->correo;
        });
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->correo, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
