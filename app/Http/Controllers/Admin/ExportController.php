<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function results(Request $request): StreamedResponse
    {
        $admin = Admin::query()->find($request->session()->get('admin_id'));

        AuditLog::create([
            'actor_id' => $admin?->id,
            'action' => 'RESULTS_EXPORTED',
            'entity_type' => 'result',
            'entity_id' => null,
            'before_data' => null,
            'after_data' => ['format' => 'csv'],
        ]);

        $filename = 'sl-assessment-results-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'code',
                'participant',
                'discord_user_id',
                'code_status',
                'batch',
                'source',
                'community_fit',
                'competitive_fit',
                'risk_score',
                'risk_level',
                'honesty_status',
                'member_type',
                'profile_code',
                'profile_name',
                'final_status',
                'completed_at',
            ]);

            AccessCode::query()
                ->with(['participant.result'])
                ->with('batch')
                ->orderBy('created_at')
                ->chunk(100, function ($codes) use ($handle): void {
                    foreach ($codes as $code) {
                        $participant = $code->participant;
                        $result = $participant?->result;

                        fputcsv($handle, [
                            $code->display_code,
                            $participant?->display_name,
                            $participant?->discord_user_id ?: $participant?->discord_username,
                            $code->status,
                            $code->batch?->name,
                            $code->batch?->source,
                            $result?->community_fit_score,
                            $result?->competitive_fit_score,
                            $result?->risk_score,
                            $result?->risk_level,
                            $result?->honesty_status,
                            $result?->member_type,
                            $result?->profile_code,
                            $result?->profile_name,
                            $result?->final_status,
                            $code->completed_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
