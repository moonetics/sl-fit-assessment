<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    use HasUuids;

    protected $fillable = [
        'email',
        'password_hash',
        'role',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
        ];
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(AccessCode::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AdminNote::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }
}
