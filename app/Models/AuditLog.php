<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'before_data',
        'after_data',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'actor_id');
    }
}
