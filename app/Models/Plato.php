<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_plato
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $nivel_dificultad
 * @property string|null $foto
 * @property string $precio_final
 * @property int $id_categoria
 * @property string $estado
 * @property-read Categoria|null $categoria
 * @property-read Collection<int, Receta> $recetas
 */
class Plato extends Model
{
    protected $table = 'plato';

    protected $primaryKey = 'id_plato';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_dificultad',
        'foto',
        'precio_final',
        'id_categoria',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio_final' => 'decimal:2',
            'nivel_dificultad' => 'integer',
        ];
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * @return HasMany<Receta, $this>
     */
    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'id_plato', 'id_plato');
    }

    public function pasosReceta(): ?string
    {
        foreach ($this->recetas as $receta) {
            $pasos = $receta->pasos;

            if ($pasos !== null && trim($pasos) !== '') {
                return $pasos;
            }
        }

        return null;
    }
}
