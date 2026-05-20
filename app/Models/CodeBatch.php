<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodeBatch extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'source',
        'quantity',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }
}
