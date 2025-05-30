<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    use SoftDeletes;

    protected $table = 'habitaciones';
    
    protected $fillable = [
        'hotel_id', 'nombre', 'tipo_habitacion', 'precio',
        'nro_adultos', 'nro_ninos', 'descripcion'
    ];

    // Relaciones
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidad::class);
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(OfertaHabitacion::class);
    }

    public function detallesReservas(): HasMany
    {
        return $this->hasMany(DetalleReserva::class);
    }
}