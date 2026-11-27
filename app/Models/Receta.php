<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_plato
 * @property int $id_ingrediente
 * @property string $cantidad_requerida
 * @property string $unidad_medida
 * @property string|null $pasos
 * @property-read Plato $plato
 * @property-read Ingrediente $ingrediente
 */
class Receta extends Model
{
    protected $table = 'receta';

    protected $primaryKey = 'id_plato';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_plato',
        'id_ingrediente',
        'cantidad_requerida',
        'unidad_medida',
        'pasos',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_requerida' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<Plato, $this>
     */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'id_plato', 'id_plato');
    }

    /**
     * @return BelongsTo<Ingrediente, $this>
     */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente', 'id_ingrediente');
    }
}
