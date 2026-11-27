<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_reserva
 * @property int $id_mesa
 * @property string $nombre_cliente
 * @property string|null $telefono
 * @property string $fecha_reserva
 * @property string $hora_reserva
 * @property int $cantidad_personas
 * @property string $estado
 * @property string $origen
 * @property-read Mesa $mesa
 */
class Reserva extends Model
{
    protected $table = 'reserva';

    protected $primaryKey = 'id_reserva';

    public $timestamps = false;

    protected $fillable = [
        'id_mesa',
        'nombre_cliente',
        'telefono',
        'fecha_reserva',
        'hora_reserva',
        'cantidad_personas',
        'estado',
        'origen',
    ];

    /** @return BelongsTo<Mesa, $this> */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'id_mesa', 'id_mesa');
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function esDesdeMesa(): bool
    {
        return $this->origen === 'mesa';
    }
}
