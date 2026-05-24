<?php

namespace App\Http\Controllers;

use App\Models\AccessCode;
use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\Participant;
use App\Models\Question;
use App\Services\AnswerRecorder;
use App\Services\AssessmentScoringService;
use App\Services\QuestionOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParticipantAssessmentController extends Controller
{
    public function validateCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_code' => ['required', 'string', 'max:32'],
        ]);

        $displayCode = $this->normalizeCode($validated['access_code']);
        $accessCode = AccessCode::query()
            ->where('code_hash', $this->hashCode($displayCode))
            ->first();

        if (! $accessCode) {
            return back()
                ->withInput()
                ->withErrors(['access_code' => 'Kode assessment tidak ditemukan.']);
        }

        if ($accessCode->expires_at?->isPast() && $accessCode->status === AccessCode::STATUS_UNUSED) {
            $accessCode->update(['status' => AccessCode::STATUS_EXPIRED]);
        }

        return match ($accessCode->fresh()->status) {
            AccessCode::STATUS_UNUSED => $this->storeAccessCodeSession($accessCode, 'assessment.instructions'),
            AccessCode::STATUS_IN_PROGRESS => $this->resumeAssessment($accessCode),
            AccessCode::STATUS_COMPLETED => back()
                ->withInput()
                ->withErrors(['access_code' => 'Assessment untuk kode ini sudah selesai dan tidak bisa digunakan ulang.']),
            AccessCode::STATUS_EXPIRED => back()
                ->withInput()
                ->withErrors(['access_code' => 'Kode assessment sudah expired. Hubungi admin Squad Limpul.']),
            AccessCode::STATUS_LOCKED => back()
                ->withInput()
                ->withErrors(['access_code' => 'Kode assessment terkunci. Hubungi admin Squad Limpul.']),
            default => back()
                ->withInput()
                ->withErrors(['access_code' => 'Kode assessment belum bisa digunakan.']),
        };
    }

    public function instructions(Request $request): View|RedirectResponse
    {
        $accessCode = $this->currentAccessCode($request);

        if (! $accessCode) {
            return redirect()->route('landing');
        }

        if ($accessCode->status === AccessCode::STATUS_COMPLETED) {
            return redirect()->route('assessment.completion');
        }

        if (in_array($accessCode->status, [AccessCode::STATUS_EXPIRED, AccessCode::STATUS_LOCKED], true)) {
            return redirect()->route('landing')
                ->withErrors(['access_code' => 'Kode assessment tidak tersedia. Hubungi admin Squad Limpul.']);
        }

        return view('assessment.instructions', [
            'participant' => $accessCode->participant,
            'accessCode' => $accessCode,
        ]);
    }

    public function start(Request $request, QuestionOrderService $questionOrder): RedirectResponse
    {
        $accessCode = $this->currentAccessCode($request);

        if (! $accessCode) {
            return redirect()->route('landing');
        }

        if (! in_array($accessCode->status, [AccessCode::STATUS_UNUSED, AccessCode::STATUS_IN_PROGRESS], true)) {
            return redirect()->route('landing')
                ->withErrors(['access_code' => 'Kode assessment tidak bisa digunakan.']);
        }

        if (! $accessCode->assigned_name || ! $accessCode->assigned_discord_id) {
            return back()
                ->withErrors(['access_code' => 'Kode assessment ini belum memiliki data peserta lengkap. Hubungi admin Squad Limpul.']);
        }

        $participant = DB::transaction(function () use ($accessCode): Participant {
            $participant = Participant::query()->updateOrCreate(
                ['access_code_id' => $accessCode->id],
                [
                    'display_name' => $accessCode->assigned_name,
                    'discord_username' => $accessCode->assigned_discord_id,
                    'discord_user_id' => $accessCode->assigned_discord_id,
                ],
            );

            if ($accessCode->status === AccessCode::STATUS_UNUSED) {
                $accessCode->update([
                    'status' => AccessCode::STATUS_IN_PROGRESS,
                    'started_at' => now(),
                ]);
            }

            return $participant;
        });

        $request->session()->put('participant_id', $participant->id);
        $questionOrder->ensureSnapshot($participant);

        if (! $this->registerAssessmentSession($request, $participant)) {
            return redirect()->route('landing')
                ->withErrors(['access_code' => 'Kode assessment terkunci karena terlalu banyak device. Hubungi admin Squad Limpul.']);
        }

        return redirect()->route('assessment.questions.show', [
            'order' => $this->firstUnansweredOrder($participant, $questionOrder),
        ]);
    }

    public function showQuestion(Request $request, QuestionOrderService $questionOrder, int $order): View|RedirectResponse
    {
        $participant = $this->currentParticipant($request);

        if (! $participant) {
            return redirect()->route('landing');
        }

        if ($participant->accessCode->status === AccessCode::STATUS_COMPLETED) {
            return redirect()->route('assessment.completion');
        }

        if ($participant->accessCode->status === AccessCode::STATUS_LOCKED) {
            return redirect()->route('landing')
                ->withErrors(['access_code' => 'Kode assessment terkunci. Hubungi admin Squad Limpul.']);
        }

        $question = $questionOrder->questionForOrder($participant, $order);

        if (! $question) {
            return redirect()->route('assessment.review');
        }

        $this->touchCurrentAssessmentSession($request);

        $answer = Answer::query()
            ->where('participant_id', $participant->id)
            ->where('question_id', $question->id)
            ->first();

        $total = $questionOrder->total($participant);
        $answeredOrders = $this->answeredOrders($participant, $questionOrder);

        return view('assessment.question', [
            'question' => $question->toParticipantPayload(),
            'answerValue' => $answer?->answer_value,
            'participantId' => $participant->id,
            'order' => $order,
            'total' => $total,
            'progress' => (int) round(($order / $total) * 100),
            'answeredOrders' => $answeredOrders,
            'missingOrders' => $this->missingOrders($total, $answeredOrders),
            'glossaryNotes' => $this->glossaryNotes($question),
        ]);
    }

    public function answerQuestion(Request $request, AnswerRecorder $answerRecorder, QuestionOrderService $questionOrder, int $order): RedirectResponse
    {
        $participant = $this->currentParticipant($request);

        if (! $participant) {
            return redirect()->route('landing');
        }

        if ($participant->accessCode->status === AccessCode::STATUS_COMPLETED) {
            return redirect()->route('assessment.completion');
        }

        $question = $questionOrder->questionForOrder($participant, $order);

        if (! $question) {
            return redirect()->route('assessment.review');
        }

        $allowedAnswers = array_map('strval', array_keys($question->public_options ?? []));

        $validated = $request->validate([
            'answer_value' => ['nullable', Rule::in($allowedAnswers)],
            'direction' => ['required', Rule::in(['back', 'next', 'skip'])],
            'answer_started_at' => ['nullable', 'date'],
            'client_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'visibility_change_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'offline_sync_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        if (! empty($validated['answer_value'])) {
            $answerRecorder->record(
                $participant,
                $question,
                (string) $validated['answer_value'],
                null,
                'saved',
                isset($validated['answer_started_at']) ? Carbon::parse($validated['answer_started_at']) : null,
                $validated['client_duration_seconds'] ?? null,
                $validated['visibility_change_count'] ?? 0,
                $validated['offline_sync_count'] ?? 0,
            );
        }

        if ($validated['direction'] === 'back') {
            return redirect()->route('assessment.questions.show', [
                'order' => max(1, $order - 1),
            ]);
        }

        if ($order >= $questionOrder->total($participant)) {
            return redirect()->route('assessment.review');
        }

        return redirect()->route('assessment.questions.show', [
            'order' => $order + 1,
        ]);
    }

    public function review(Request $request, QuestionOrderService $questionOrder): View|RedirectResponse
    {
        $participant = $this->currentParticipant($request);

        if (! $participant) {
            return redirect()->route('landing');
        }

        if ($participant->accessCode->status === AccessCode::STATUS_COMPLETED) {
            return redirect()->route('assessment.completion');
        }

        $answered = Answer::query()
            ->where('participant_id', $participant->id)
            ->count();
        $total = $questionOrder->total($participant);
        $missing = max(0, $total - $answered);
        $answeredOrders = $this->answeredOrders($participant, $questionOrder);

        return view('assessment.review', [
            'answered' => $answered,
            'missing' => $missing,
            'total' => $total,
            'firstMissingOrder' => $this->firstUnansweredOrder($participant, $questionOrder),
            'missingOrders' => $this->missingOrders($total, $answeredOrders),
            'submissionAttemptId' => (string) Str::uuid(),
        ]);
    }

    public function submit(Request $request, AssessmentScoringService $scoringService, QuestionOrderService $questionOrder): RedirectResponse
    {
        $participant = $this->currentParticipant($request);

        if (! $participant) {
            return redirect()->route('landing');
        }

        if ($participant->accessCode->status === AccessCode::STATUS_COMPLETED) {
            return redirect()->route('assessment.completion');
        }

        $request->validate([
            'final_confirmation' => ['accepted'],
        ]);

        $firstMissingOrder = $this->firstUnansweredOrder($participant, $questionOrder);

        if ($firstMissingOrder <= $questionOrder->total($participant)) {
            return redirect()->route('assessment.questions.show', ['order' => $firstMissingOrder])
                ->withErrors(['answer_value' => 'Lengkapi semua soal sebelum submit final.']);
        }

        $validated = $request->validate([
            'submission_attempt_id' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($participant, $validated, $scoringService): void {
            $accessCode = $participant->accessCode()->lockForUpdate()->first();

            if ($accessCode->status === AccessCode::STATUS_COMPLETED) {
                return;
            }

            $accessCode->update([
                'status' => AccessCode::STATUS_COMPLETED,
                'completed_at' => now(),
                'submission_attempt_id' => $validated['submission_attempt_id'],
            ]);

            $scoringService->score($participant->fresh(['accessCode', 'sessions']));
        });

        $request->session()->put('completed_participant_id', $participant->id);
        $request->session()->forget(['assessment_session_id']);

        return redirect()->route('assessment.completion');
    }

    public function completion(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('completed_participant_id')) {
            return redirect()->route('landing');
        }

        return view('assessment.completion');
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($code)));
    }

    private function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }

    private function storeAccessCodeSession(AccessCode $accessCode, string $routeName): RedirectResponse
    {
        session([
            'access_code_id' => $accessCode->id,
        ]);

        return redirect()->route($routeName);
    }

    private function resumeAssessment(AccessCode $accessCode): RedirectResponse
    {
        $participant = $accessCode->participant;

        if (! $participant) {
            return $this->storeAccessCodeSession($accessCode, 'assessment.instructions');
        }

        session([
            'access_code_id' => $accessCode->id,
            'participant_id' => $participant->id,
        ]);

        if (! $this->registerAssessmentSession(request(), $participant, true)) {
            return redirect()->route('landing')
                ->withErrors(['access_code' => 'Kode assessment terkunci karena terlalu banyak device. Hubungi admin Squad Limpul.']);
        }

        return redirect()->route('assessment.questions.show', [
            'order' => $this->firstUnansweredOrder($participant, app(QuestionOrderService::class)),
        ]);
    }

    private function currentAccessCode(Request $request): ?AccessCode
    {
        $id = $request->session()->get('access_code_id');

        if (! $id) {
            return null;
        }

        return AccessCode::query()->with('participant')->find($id);
    }

    private function currentParticipant(Request $request): ?Participant
    {
        $id = $request->session()->get('participant_id');

        if (! $id) {
            return null;
        }

        return Participant::query()->with('accessCode')->find($id);
    }

    private function firstUnansweredOrder(Participant $participant, QuestionOrderService $questionOrder): int
    {
        $answeredQuestionIds = Answer::query()
            ->where('participant_id', $participant->id)
            ->pluck('question_id')
            ->all();

        foreach ($questionOrder->ensureSnapshot($participant) as $index => $questionNumber) {
            $questionId = Question::query()
                ->where('is_active', true)
                ->where('question_number', $questionNumber)
                ->value('id');

            if ($questionId && ! in_array($questionId, $answeredQuestionIds, true)) {
                return $index + 1;
            }
        }

        return $questionOrder->total($participant) + 1;
    }

    /**
     * @return array<int, bool>
     */
    private function answeredOrders(Participant $participant, QuestionOrderService $questionOrder): array
    {
        $answeredOrders = [];

        Answer::query()
            ->where('participant_id', $participant->id)
            ->pluck('question_id')
            ->each(function (string $questionId) use (&$answeredOrders, $participant, $questionOrder): void {
                $order = $questionOrder->orderForQuestion($participant, $questionId);

                if ($order) {
                    $answeredOrders[$order] = true;
                }
            });

        return $answeredOrders;
    }

    /**
     * @param  array<int, bool>  $answeredOrders
     * @return array<int, int>
     */
    private function missingOrders(int $total, array $answeredOrders): array
    {
        $missing = [];

        for ($order = 1; $order <= $total; $order++) {
            if (! isset($answeredOrders[$order])) {
                $missing[] = $order;
            }
        }

        return $missing;
    }

    /**
     * @return array<string, string>
     */
    private function glossaryNotes(Question $question): array
    {
        $notes = [];
        $displayedText = implode(' ', $this->displayedQuestionText($question));

        foreach (config('assessment_glossary') as $term => $description) {
            if ($this->containsGlossaryTerm($displayedText, $term)) {
                $notes[$term] = $description;
            }
        }

        return $notes;
    }

    /**
     * @return array<int, string>
     */
    private function displayedQuestionText(Question $question): array
    {
        $text = [$question->text];

        foreach ($question->public_options ?? [] as $option) {
            if (is_scalar($option)) {
                $text[] = (string) $option;
            }
        }

        return $text;
    }

    private function containsGlossaryTerm(string $text, string $term): bool
    {
        $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($term, '/').'(?![\p{L}\p{N}])/iu';

        return preg_match($pattern, $text) === 1;
    }

    private function deviceId(Request $request): string
    {
        $rawDeviceId = $request->header('X-SL-Device-ID')
            ?: $request->cookie('sl_device_id')
            ?: $request->session()->get('sl_device_id');

        if (! $rawDeviceId) {
            $rawDeviceId = (string) Str::uuid();
            $request->session()->put('sl_device_id', $rawDeviceId);
        }

        return hash('sha256', implode('|', [
            $rawDeviceId,
            $request->userAgent() ?? 'unknown-agent',
        ]));
    }

    private function registerAssessmentSession(Request $request, Participant $participant, bool $isResume = false): bool
    {
        $deviceId = $this->deviceId($request);

        AssessmentSession::query()
            ->where('participant_id', $participant->id)
            ->update(['is_writer' => false]);

        $assessmentSession = AssessmentSession::query()
            ->where('participant_id', $participant->id)
            ->where('device_id', $deviceId)
            ->first();

        if ($assessmentSession) {
            $assessmentSession->resume_count += $isResume ? 1 : 0;
            $assessmentSession->last_seen_at = now();
            $assessmentSession->is_active = true;
            $assessmentSession->is_writer = true;
            $assessmentSession->save();
        } else {
            $assessmentSession = AssessmentSession::create([
                'participant_id' => $participant->id,
                'session_token_hash' => hash('sha256', Str::random(64)),
                'device_id' => $deviceId,
                'user_agent' => $request->userAgent(),
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
                'started_at' => now(),
                'last_seen_at' => now(),
                'resume_count' => $isResume ? 1 : 0,
                'is_writer' => true,
            ]);
        }

        $request->session()->put('assessment_session_id', $assessmentSession->id);

        $deviceCount = AssessmentSession::query()
            ->where('participant_id', $participant->id)
            ->distinct('device_id')
            ->count('device_id');

        if ($deviceCount > 2) {
            $participant->accessCode->update([
                'status' => AccessCode::STATUS_LOCKED,
                'locked_reason' => 'Device limit exceeded during participant assessment.',
            ]);

            return false;
        }

        return true;
    }

    private function touchCurrentAssessmentSession(Request $request): void
    {
        $sessionId = $request->session()->get('assessment_session_id');

        if (! $sessionId) {
            return;
        }

        AssessmentSession::query()
            ->where('id', $sessionId)
            ->increment('refresh_count', 1, [
                'last_seen_at' => now(),
            ]);
    }
}
