<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperadorHotel extends Model
{
    protected $table = 'operadores_hoteles';
    
    protected $fillable = [
        'user_id', 'hotel_id', 'nombreHotel'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function administradores(): HasMany
    {
        return $this->hasMany(AdministradorHotel::class, 'operador_hotel_id');
    }
}