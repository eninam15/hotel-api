<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hotel extends Model
{
    use SoftDeletes;

    protected $table = 'hoteles';
    
    protected $fillable = [
        'nombre', 'categoria', 'direccion', 'telefono', 
        'foto', 'ciudad', 'hr_entrada', 'hr_salida', 'publicar'
    ];

    // Relaciones
    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'hotel_servicio');
    }

    public function operadores(): HasMany
    {
        return $this->hasMany(OperadorHotel::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function ranking(): HasOne
    {
        return $this->hasOne(Ranking::class);
    }

    public function depositos(): HasMany
    {
        return $this->hasMany(Deposito::class);
    }
}