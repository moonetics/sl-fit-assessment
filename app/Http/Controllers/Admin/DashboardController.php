<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\CodeBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $query = AccessCode::query()
            ->with(['participant.result', 'batch'])
            ->latest();

        $this->applyFilters($query, $request);

        return view('admin.dashboard', [
            'codes' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'risk_level', 'final_status', 'batch_id', 'source']),
            'codeStatuses' => AccessCode::STATUSES,
            'riskLevels' => config('assessment_scoring.risk_levels'),
            'finalStatuses' => config('assessment_scoring.final_statuses'),
            'batches' => CodeBatch::query()->latest()->get(),
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('risk_level')) {
            $query->whereHas('participant.result', fn (Builder $result): Builder => $result->where('risk_level', $request->string('risk_level')));
        }

        if ($request->filled('final_status')) {
            $query->whereHas('participant.result', fn (Builder $result): Builder => $result->where('final_status', $request->string('final_status')));
        }

        if ($request->filled('batch_id')) {
            $query->where('code_batch_id', $request->string('batch_id'));
        }

        if ($request->filled('source')) {
            $query->whereHas('batch', fn (Builder $batch): Builder => $batch->where('source', 'like', '%'.$request->string('source')->trim().'%'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';

            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('display_code', 'like', $term)
                    ->orWhere('assigned_name', 'like', $term)
                    ->orWhere('assigned_discord_id', 'like', $term)
                    ->orWhereHas('participant', function (Builder $participant) use ($term): void {
                        $participant
                            ->where('display_name', 'like', $term)
                            ->orWhere('discord_username', 'like', $term)
                            ->orWhere('discord_user_id', 'like', $term);
                    });
            });
        }
    }
}
