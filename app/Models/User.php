<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Documento',
        'password',
        'Rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function asociado()
    {
        return $this->hasOne(Asociado::class);
    }

    public function adherente()
    {
        return $this->hasOne(Adherente::class);
    }

    public function familiar()
    {
        return $this->hasOne(Familiar::class);
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class);
    }

    public function estado()
    {
        return $this->hasMany(Estados::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitudes::class);
    }

    public function mensualidades()
    {
        return $this->hasMany(Mensualidades::class);
    }

    public function cuotas()
    {
        return $this->hasMany(CuotasBaile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expoTokens()
    {
        return $this->hasMany(ExpoToken::class);
    }

    public function routeNotificationForExpo($notification): array
    {
        return $this->expoTokens()->where('enabled', true)->pluck('token')->toArray();
    }
}
