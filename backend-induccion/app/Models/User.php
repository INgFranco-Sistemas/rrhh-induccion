<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin | trabajador
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // === JWT ===
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
        ];
    }

    // === RELACIONES ===

    public function progresos(): HasMany
    {
        return $this->hasMany(VideoUserProgress::class);
    }

    public function declaracionesJuradas(): HasMany
    {
        return $this->hasMany(DeclaracionJurada::class);
    }
}
