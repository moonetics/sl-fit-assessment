<?php

namespace App\Http\Controllers\Api\Participant;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\AssessmentSession;
use App\Models\Participant;
use App\Services\AnswerRecorder;
use App\Services\QuestionOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AnswerController extends Controller
{
    public function autosave(Request $request, AnswerRecorder $answerRecorder, QuestionOrderService $questionOrder): JsonResponse
    {
        $participantId = $request->session()->get('participant_id');

        if (! $participantId) {
            return response()->json([
                'message' => 'Assessment session tidak ditemukan.',
            ], 401);
        }

        $participant = Participant::query()
            ->with('accessCode')
            ->find($participantId);

        if (! $participant) {
            return response()->json([
                'message' => 'Participant tidak ditemukan.',
            ], 404);
        }

        $assessmentSessionId = $request->session()->get('assessment_session_id');

        if ($assessmentSessionId) {
            $isWriter = AssessmentSession::query()
                ->where('id', $assessmentSessionId)
                ->where('participant_id', $participant->id)
                ->where('is_writer', true)
                ->exists();

            if (! $isWriter) {
                return response()->json([
                    'message' => 'Session ini bukan writer aktif. Refresh assessment untuk melanjutkan.',
                ], 409);
            }
        }

        if (in_array($participant->accessCode->status, [
            AccessCode::STATUS_COMPLETED,
            AccessCode::STATUS_EXPIRED,
            AccessCode::STATUS_LOCKED,
        ], true)) {
            return response()->json([
                'message' => 'Assessment tidak tersedia untuk autosave.',
            ], 409);
        }

        $validated = $request->validate([
            'display_order' => ['required', 'integer', 'min:1', 'max:76'],
            'answer_value' => ['required', 'string', 'max:10'],
            'client_saved_at' => ['nullable', 'date'],
            'draft_id' => ['nullable', 'string', 'max:80'],
            'answer_started_at' => ['nullable', 'date'],
            'client_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'visibility_change_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'offline_sync_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $question = $questionOrder->questionForOrder($participant, (int) $validated['display_order']);

        if (! $question) {
            return response()->json([
                'message' => 'Soal assessment tidak ditemukan.',
            ], 404);
        }

        $allowedAnswers = array_map('strval', array_keys($question->public_options ?? []));

        $request->validate([
            'answer_value' => [Rule::in($allowedAnswers)],
        ]);

        $answer = $answerRecorder->record(
            $participant,
            $question,
            (string) $validated['answer_value'],
            isset($validated['client_saved_at']) ? Carbon::parse($validated['client_saved_at']) : null,
            'saved',
            isset($validated['answer_started_at']) ? Carbon::parse($validated['answer_started_at']) : null,
            $validated['client_duration_seconds'] ?? null,
            $validated['visibility_change_count'] ?? 0,
            $validated['offline_sync_count'] ?? 0,
        );

        return response()->json([
            'message' => 'Tersimpan',
            'answer' => [
                'display_order' => $question->display_order,
                'answer_value' => $answer->answer_value,
                'saved_at' => $answer->saved_at?->toIso8601String(),
                'revision' => $answer->revision,
                'sync_status' => $answer->sync_status,
            ],
        ]);
    }
}
