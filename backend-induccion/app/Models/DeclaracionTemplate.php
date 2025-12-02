<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeclaracionTemplate extends Model
{
    protected $table = 'declaracion_templates';
    protected $connection = 'induccion';

    protected $fillable = [
        'nombre',
        'file_path',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }
}
