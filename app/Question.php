<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'quiz_id', 'type', 'prompt', 'marks',
        'image_path', 'video_url', 'config', 'position',
    ];

    protected $casts = [
        'config' => 'array',
        'marks' => 'float',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('position')->orderBy('id');
    }
}
