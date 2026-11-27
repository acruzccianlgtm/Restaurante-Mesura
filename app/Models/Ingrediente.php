<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_ingrediente
 * @property string $nombre
 * @property string $unidad_medida_almacen
 * @property string $cantidad_actual_almacen
 * @property string $cantidad_minima
 * @property string $estado
 * @property-read string $nivel
 */
class Ingrediente extends Model
{
    protected $table = 'ingrediente';

    protected $primaryKey = 'id_ingrediente';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'unidad_medida_almacen',
        'cantidad_actual_almacen',
        'cantidad_minima',
        'estado',
    ];

    protected $casts = [
        'cantidad_actual_almacen' => 'decimal:2',
        'cantidad_minima' => 'decimal:2',
    ];

    public function getNivelAttribute(): string
    {
        if (! $this->estaActivo()) {
            return 'Inactivo';
        }

        if ((float) $this->cantidad_actual_almacen <= 0) {
            return 'Agotado';
        }

        if ((float) $this->cantidad_actual_almacen <= (float) $this->cantidad_minima) {
            return 'Bajo';
        }

        return 'Normal';
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
