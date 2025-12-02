<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeclaracionJurada extends Model
{
    protected $table = 'declaraciones_juradas';
    protected $connection = 'induccion';

    protected $fillable = [
        'user_id',
        'texto_declaracion',
        'ip_address',
        'user_agent',
        'firmado_at',
    ];

    protected $casts = [
        'firmado_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
