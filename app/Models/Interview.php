<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasUuids;

    public const OUTCOMES = [
        'Proceed',
        'Keep Manual Review',
        'Trial Recommended',
        'Watchlist Recommended',
        'Retest Recommended',
        'Reject Recommended',
    ];

    protected $fillable = [
        'participant_id',
        'admin_id',
        'interviewer_name',
        'interview_at',
        'questions_summary',
        'answers_summary',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'interview_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
