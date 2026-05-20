<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use HasUuids;

    protected $fillable = [
        'access_code_id',
        'display_name',
        'discord_username',
        'question_order_snapshot',
        'discord_user_id',
        'discord_verified_at',
        'discord_metadata',
    ];

    protected function casts(): array
    {
        return [
            'question_order_snapshot' => 'array',
            'discord_verified_at' => 'datetime',
            'discord_metadata' => 'array',
        ];
    }

    public function accessCode(): BelongsTo
    {
        return $this->belongsTo(AccessCode::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AssessmentSession::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AdminNote::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
