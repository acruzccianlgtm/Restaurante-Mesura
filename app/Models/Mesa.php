<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_mesa
 * @property int $capacidad
 * @property string $estado
 * @property-read Collection<int, Reserva> $reservas
 */
class Mesa extends Model
{
    protected $table = 'mesa';

    protected $primaryKey = 'id_mesa';

    public $timestamps = false;

    protected $fillable = [
        'capacidad',
        'estado',
    ];

    /** @return HasMany<Reserva, $this> */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'id_mesa', 'id_mesa');
    }

    /** @return HasMany<Reserva, $this> */
    public function reservasActivas(): HasMany
    {
        return $this->reservas()->where('estado', 'activa');
    }
}
