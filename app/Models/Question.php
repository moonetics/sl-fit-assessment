<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasUuids;

    protected $fillable = [
        'question_number',
        'display_order',
        'text',
        'question_type',
        'category',
        'subcategory',
        'scoring_direction',
        'options',
        'public_options',
        'scoring_map',
        'red_flag_options',
        'risk_tags',
        'consistency_pair',
        'consistency_pair_id',
        'consistency_check',
        'admin_notes',
        'research_basis',
        'profile_axis',
        'profile_pole',
        'is_consistency_item',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'public_options' => 'array',
            'scoring_map' => 'array',
            'red_flag_options' => 'array',
            'risk_tags' => 'array',
            'consistency_pair' => 'array',
            'is_consistency_item' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActiveForParticipant(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('display_order')
            ->select([
                'display_order',
                'text',
                'question_type',
                'public_options',
            ]);
    }

    /**
     * @return array{display_order: int|null, text: string, question_type: string, public_options: array<mixed>|null}
     */
    public function toParticipantPayload(): array
    {
        return [
            'display_order' => $this->display_order,
            'text' => $this->text,
            'question_type' => $this->question_type,
            'public_options' => $this->public_options,
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
