<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'fec_nacimiento',
        'pais',
        'telefono',
        'foto_perfil',
        'direccion',
        'sexo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'fec_nacimiento' => 'date',
    ];

    // Relaciones
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    public function operadorHoteles(): HasMany
    {
        return $this->hasMany(OperadorHotel::class);
    }

    public function administradorHoteles(): HasMany
    {
        return $this->hasMany(AdministradorHotel::class);
    }

    public function billeteraElectronica(): HasMany
    {
        return $this->hasMany(BilleteraElectronica::class);
    }
}