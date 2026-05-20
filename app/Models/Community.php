<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'branding',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class);
    }
}
