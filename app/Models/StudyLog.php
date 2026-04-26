<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class StudyLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'topic',
        'category',
        'study_duration_minutes',
        'mood',
        'distraction_level',
        'notes',
        'ai_feedback',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}