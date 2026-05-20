<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'participant_id',
        'session_token_hash',
        'device_id',
        'user_agent',
        'ip_hash',
        'started_at',
        'last_seen_at',
        'refresh_count',
        'resume_count',
        'is_active',
        'is_writer',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_active' => 'boolean',
            'is_writer' => 'boolean',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
