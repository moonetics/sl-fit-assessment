<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccessCode extends Model
{
    use HasUuids;

    public const STATUS_UNUSED = 'Unused';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_EXPIRED = 'Expired';
    public const STATUS_LOCKED = 'Locked';

    public const STATUSES = [
        self::STATUS_UNUSED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_EXPIRED,
        self::STATUS_LOCKED,
    ];

    protected $fillable = [
        'code_hash',
        'display_code',
        'assigned_name',
        'assigned_discord_id',
        'status',
        'expires_at',
        'created_by',
        'code_batch_id',
        'community_id',
        'started_at',
        'completed_at',
        'locked_reason',
        'submission_attempt_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: function (string $value): string {
                if (! in_array($value, self::STATUSES, true)) {
                    throw new InvalidArgumentException("Invalid access code status [{$value}].");
                }

                return $value;
            },
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CodeBatch::class, 'code_batch_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class);
    }
}
