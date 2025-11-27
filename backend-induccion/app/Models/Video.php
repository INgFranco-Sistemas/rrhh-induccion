<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'file_path',
        'orden',
        'duracion_segundos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_segundos' => 'integer',
    ];

    // Para que en el JSON aparezca "url"
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    // Un video tiene muchos registros de progreso (uno por usuario)
    public function progresos(): HasMany
    {
        return $this->hasMany(VideoUserProgress::class);
    }
}
