<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdministradorHotel extends Model
{
    protected $table = 'administradores_hoteles';
    
    protected $fillable = [
        'user_id', 'operador_hotel_id'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(OperadorHotel::class, 'operador_hotel_id');
    }
}