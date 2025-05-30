<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposito extends Model
{
    protected $table = 'depositos';
    
    protected $fillable = [
        'hotel_id', 'nro_transaccion', 'banco', 
        'monto', 'fecha', 'verificado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'verificado' => 'boolean',
    ];

    // Relaciones
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}