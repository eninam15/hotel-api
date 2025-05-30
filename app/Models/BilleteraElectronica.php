<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BilleteraElectronica extends Model
{
    protected $table = 'billeteras_electronicas';
    
    protected $fillable = [
        'user_id', 'fecha', 'ingreso', 'egreso', 
        'monto', 'tipo_transaccion', 'descripcion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'ingreso' => 'boolean',
        'egreso' => 'boolean',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}