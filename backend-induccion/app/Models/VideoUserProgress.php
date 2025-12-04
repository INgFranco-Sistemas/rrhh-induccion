<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoUserProgress extends Model
{
    protected $table = 'video_user_progress'; // nombre explícito
    protected $connection = 'induccion';

    protected $fillable = [
        'user_id',
        'video_id',
        'segundos_vistos',
        'completado',
        'completado_at',
    ];

    protected $casts = [
        'segundos_vistos' => 'integer',
        'completado' => 'boolean',
        'completado_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
