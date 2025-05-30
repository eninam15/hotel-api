<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfertaHabitacion extends Model
{
    protected $table = 'ofertas_habitaciones';
    
    protected $fillable = [
        'servicio_id', 'habitacion_id', 'descuento', 
        'fec_inicio', 'fec_fin'
    ];

    protected $casts = [
        'fec_inicio' => 'date',
        'fec_fin' => 'date',
    ];

    // Relaciones
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}