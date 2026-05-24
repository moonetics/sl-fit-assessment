<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AssessmentSetting;
use App\Models\AuditLog;
use App\Services\AssessmentSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScoringSettingsController extends Controller
{
    public function edit(AssessmentSettingsService $settings): View
    {
        return view('admin.scoring-settings.edit', [
            'thresholds' => $settings->thresholds(),
            'defaults' => config('assessment_scoring.thresholds'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_duration_minutes' => ['required', 'numeric', 'min:1', 'max:120'],
            'high_speed_minutes' => ['required', 'numeric', 'min:1', 'max:120'],
            'straight_lining_medium' => ['required', 'numeric', 'min:0.5', 'max:1'],
            'straight_lining_high' => ['required', 'numeric', 'min:0.5', 'max:1'],
            'perfection_medium' => ['required', 'numeric', 'min:0.5', 'max:1'],
            'perfection_high' => ['required', 'numeric', 'min:0.5', 'max:1'],
            'refresh_count' => ['required', 'integer', 'min:1', 'max:500'],
            'resume_count' => ['required', 'integer', 'min:1', 'max:100'],
            'device_count' => ['required', 'integer', 'min:1', 'max:10'],
            'min_answer_seconds' => ['required', 'integer', 'min:0', 'max:60'],
            'fast_answer_count' => ['required', 'integer', 'min:1', 'max:96'],
            'visibility_change_count' => ['required', 'integer', 'min:1', 'max:500'],
            'offline_sync_count' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $admin = Admin::query()->find($request->session()->get('admin_id'));
        $setting = AssessmentSetting::query()->firstOrNew(['key' => 'scoring_thresholds']);
        $before = $setting->exists ? $setting->toArray() : null;

        $setting->fill([
            'value' => $validated,
            'updated_by' => $admin?->id,
        ])->save();

        AuditLog::create([
            'actor_id' => $admin?->id,
            'action' => 'SCORING_SETTINGS_UPDATED',
            'entity_type' => 'assessment_setting',
            'entity_id' => $setting->id,
            'before_data' => $before,
            'after_data' => $setting->fresh()->toArray(),
        ]);

        return back()->with('status', 'Scoring thresholds berhasil diperbarui.');
    }
}
