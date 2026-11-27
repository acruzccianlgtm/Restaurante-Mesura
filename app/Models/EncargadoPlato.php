<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_encargado
 * @property string $nombre
 * @property string $apellido
 * @property int|null $edad
 * @property string|null $cargo
 * @property string|null $telefono
 * @property string|null $ciudad
 * @property string $estado
 * @property-read int|null $categorias_count
 * @property-read string $nombre_completo
 */
class EncargadoPlato extends Model
{
    protected $table = 'encargado_plato';

    protected $primaryKey = 'id_encargado';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'edad',
        'cargo',
        'telefono',
        'ciudad',
        'estado',
    ];

    /**
     * @return HasMany<Categoria, $this>
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'id_encargado', 'id_encargado');
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }
}
