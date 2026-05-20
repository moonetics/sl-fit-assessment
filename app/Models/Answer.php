<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasUuids;

    protected $fillable = [
        'participant_id',
        'question_id',
        'answer_value',
        'score_value',
        'revision',
        'saved_at',
        'client_saved_at',
        'answer_started_at',
        'client_duration_seconds',
        'visibility_change_count',
        'offline_sync_count',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
            'client_saved_at' => 'datetime',
            'answer_started_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
