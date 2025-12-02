<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    use HasFactory;

    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    //     'role', // admin | trabajador
    // ];

        protected $fillable = [
		'adm_name', 'adm_lastname', 'adm_email', 'adm_dni', 'adm_inicial', 'adm_estado', 'adm_cargo', 'depe_id', 'adm_vigencia', 'adm_observacion', 'adm_tipo', 'adm_caseta', 'adm_esjefe', 'adm_telefono', 'adm_direccion', 'adm_con_especialidad', 'darkmode', 'push_id', 'avatar', 'adm_correo',
	];

    protected $table='admin';

    protected $hidden = [
        'password',
        'remember_token',
    ];

        protected $appends = ['fullname'];

	public function getFullNameAttribute()
	{
		return $this->adm_name . ' ' . $this->adm_lastname;

	}

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'depe_id','iddependencia')->select('iddependencia','depe_nombre','depe_depende');
    }

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
