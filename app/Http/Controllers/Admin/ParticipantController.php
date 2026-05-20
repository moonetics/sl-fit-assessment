<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminNote;
use App\Models\AuditLog;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function show(Participant $participant): View
    {
        $participant->load([
            'accessCode',
            'answers.question',
            'result.overrideAdmin',
            'sessions',
            'notes.admin',
            'interviews.admin',
        ]);

        return view('admin.participants.show', [
            'participant' => $participant,
            'finalStatuses' => config('assessment_scoring.final_statuses'),
            'interviewOutcomes' => \App\Models\Interview::OUTCOMES,
        ]);
    }

    public function storeNote(Request $request, Participant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $admin = $this->admin($request);
        $note = AdminNote::create([
            'participant_id' => $participant->id,
            'admin_id' => $admin?->id,
            'note' => $validated['note'],
        ]);

        $this->audit($admin, 'ADMIN_NOTE_CREATED', 'participant', $participant->id, null, $note->toArray());

        return back()->with('status', 'Catatan admin tersimpan.');
    }

    public function updateFinalStatus(Request $request, Participant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'final_status' => ['required', Rule::in(config('assessment_scoring.final_statuses'))],
            'override_reason' => ['required', 'string', 'max:1000'],
        ]);

        $result = $participant->result;

        if (! $result) {
            return back()->withErrors(['final_status' => 'Result belum tersedia untuk participant ini.']);
        }

        $admin = $this->admin($request);
        $before = $result->toArray();

        $result->update([
            'final_status' => $validated['final_status'],
            'overridden_by' => $admin?->id,
            'override_reason' => $validated['override_reason'],
        ]);

        $this->audit($admin, 'FINAL_STATUS_OVERRIDDEN', 'result', $result->id, $before, $result->fresh()->toArray());

        return back()->with('status', 'Final status berhasil di-override.');
    }

    private function admin(Request $request): ?Admin
    {
        return Admin::query()->find($request->session()->get('admin_id'));
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function audit(?Admin $admin, string $action, string $entityType, string $entityId, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'actor_id' => $admin?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
        ]);
    }
}
