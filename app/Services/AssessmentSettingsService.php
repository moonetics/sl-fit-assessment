<?php

namespace App\Services;

use App\Models\AssessmentSetting;

class AssessmentSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function scoring(): array
    {
        $base = config('assessment_scoring');
        $thresholds = AssessmentSetting::query()
            ->where('key', 'scoring_thresholds')
            ->first()
            ?->value;

        if (is_array($thresholds)) {
            $base['thresholds'] = array_replace($base['thresholds'], $thresholds);
        }

        return $base;
    }

    /**
     * @return array<string, int|float>
     */
    public function thresholds(): array
    {
        return $this->scoring()['thresholds'];
    }
}
