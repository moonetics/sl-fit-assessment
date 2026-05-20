<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
