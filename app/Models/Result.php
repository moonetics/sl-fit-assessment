<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasUuids;

    protected $fillable = [
        'participant_id',
        'community_fit_score',
        'competitive_fit_score',
        'risk_score',
        'risk_level',
        'honesty_status',
        'member_type',
        'final_status',
        'auto_final_status',
        'profile_code',
        'profile_name',
        'profile_breakdown',
        'category_scores',
        'red_flags',
        'suspicious_flags',
        'risk_reasons',
        'generated_at',
        'overridden_by',
        'override_reason',
    ];

    protected function casts(): array
    {
        return [
            'category_scores' => 'array',
            'red_flags' => 'array',
            'suspicious_flags' => 'array',
            'profile_breakdown' => 'array',
            'risk_reasons' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function overrideAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'overridden_by');
    }
}
