<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserva extends Model
{
    use SoftDeletes;
    
    protected $table = 'reservas';
    
    protected $fillable = [
        'nro_reserva', 'user_id', 'hotel_id', 'nro_noches',
        'precio_total', 'estado', 'fec_checkin', 'fec_checkout'
    ];

    protected $casts = [
        'fec_checkin' => 'date',
        'fec_checkout' => 'date',
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

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleReserva::class);
    }
}