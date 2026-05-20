<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Interview;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterviewController extends Controller
{
    public function store(Request $request, Participant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'interviewer_name' => ['required', 'string', 'max:120'],
            'interview_at' => ['nullable', 'date'],
            'questions_summary' => ['required', 'string', 'max:4000'],
            'answers_summary' => ['required', 'string', 'max:4000'],
            'outcome' => ['required', Rule::in(Interview::OUTCOMES)],
        ]);

        $admin = Admin::query()->find($request->session()->get('admin_id'));
        $interview = Interview::create([
            'participant_id' => $participant->id,
            'admin_id' => $admin?->id,
            ...$validated,
        ]);

        AuditLog::create([
            'actor_id' => $admin?->id,
            'action' => 'INTERVIEW_CREATED',
            'entity_type' => 'participant',
            'entity_id' => $participant->id,
            'before_data' => null,
            'after_data' => $interview->toArray(),
        ]);

        return back()->with('status', 'Interview note tersimpan.');
    }
}
