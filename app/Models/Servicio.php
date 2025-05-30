<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $table = 'servicios';
    
    protected $fillable = [
        'nombre', 'comision'
    ];

    // Relaciones
    public function hoteles(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'hotel_servicio');
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(OfertaHabitacion::class);
    }
}