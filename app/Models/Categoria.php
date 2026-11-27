<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_categoria
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $id_encargado
 * @property string $estado
 * @property-read int|null $platos_count
 * @property-read EncargadoPlato|null $encargado
 */
class Categoria extends Model
{
    protected $table = 'categoria';

    protected $primaryKey = 'id_categoria';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_encargado',
        'estado',
    ];

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * @return HasMany<Plato, $this>
     */
    public function platos(): HasMany
    {
        return $this->hasMany(Plato::class, 'id_categoria', 'id_categoria');
    }

    /**
     * @return BelongsTo<EncargadoPlato, $this>
     */
    public function encargado(): BelongsTo
    {
        return $this->belongsTo(EncargadoPlato::class, 'id_encargado', 'id_encargado');
    }
}
