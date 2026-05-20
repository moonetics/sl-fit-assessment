<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNote extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'participant_id',
        'admin_id',
        'note',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
