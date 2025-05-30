<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $table = 'rankings';
    
    protected $fillable = [
        'hotel_id', 'pt_general', 'nro_valoraciones', 
        'nro_reservas', 'visitas'
    ];

    // Relaciones
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}