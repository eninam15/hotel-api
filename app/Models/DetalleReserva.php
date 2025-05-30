<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleReserva extends Model
{
    protected $table = 'detalles_reservas';
    
    protected $fillable = [
        'reserva_id', 'habitacion_id', 'cantidad', 
        'precio_hab', 'fecha'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relaciones
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}