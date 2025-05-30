<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';
    
    protected $fillable = [
        'habitacion_id', 'fecha', 'disponibles', 'reservadas', 'precio'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relaciones
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }
}