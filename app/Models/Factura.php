<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    protected $table = 'facturas';
    
    protected $fillable = [
        'nro_factura', 'user_id', 'monto', 'concepto',
        'fecha', 'periodoFacturacion', 'estadoPago'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}