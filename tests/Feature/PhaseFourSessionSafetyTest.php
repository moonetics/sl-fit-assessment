<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\Participant;
use App\Models\Question;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourSessionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_autosave_endpoint_saves_answer_with_client_metadata(): void
    {
        $participant = $this->startedParticipant();
        $session = $this->assessmentSession($participant);

        $this->withSession([
            'participant_id' => $participant->id,
            'assessment_session_id' => $session->id,
        ])->putJson(route('api.answers.autosave'), [
            'display_order' => 1,
            'answer_value' => '4',
            'client_saved_at' => '2026-05-18T10:00:00+07:00',
            'draft_id' => 'draft-order-1',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Tersimpan')
            ->assertJsonPath('answer.display_order', 1)
            ->assertJsonPath('answer.answer_value', '4')
            ->assertJsonPath('answer.sync_status', 'saved');

        $this->assertDatabaseHas('answers', [
            'participant_id' => $participant->id,
            'answer_value' => '4',
            'sync_status' => 'saved',
        ]);

        $this->assertNotNull(Answer::query()->first()->client_saved_at);
    }

    public function test_autosave_rejects_invalid_answers(): void
    {
        $participant = $this->startedParticipant();
        $session = $this->assessmentSession($participant);

        $this->withSession([
            'participant_id' => $participant->id,
            'assessment_session_id' => $session->id,
        ])->putJson(route('api.answers.autosave'), [
            'display_order' => 1,
            'answer_value' => '9',
        ])->assertUnprocessable();

        $this->assertSame(0, Answer::query()->count());
    }

    public function test_autosave_accepts_dynamic_last_display_order(): void
    {
        $participant = $this->startedParticipant();
        $session = $this->assessmentSession($participant);
        $question = app(\App\Services\QuestionOrderService::class)->questionForOrder($participant, 96);

        $this->withSession([
            'participant_id' => $participant->id,
            'assessment_session_id' => $session->id,
        ])->putJson(route('api.answers.autosave'), [
            'display_order' => 96,
            'answer_value' => (string) array_key_first($question?->public_options ?? []),
        ])->assertOk();

        $this->assertSame(1, Answer::query()->where('participant_id', $participant->id)->count());
    }

    public function test_autosave_rejects_closed_access_code_statuses(): void
    {
        foreach ([AccessCode::STATUS_COMPLETED, AccessCode::STATUS_EXPIRED, AccessCode::STATUS_LOCKED] as $status) {
            $participant = $this->startedParticipant($status, "CFA-{$status}");
            $session = $this->assessmentSession($participant);

            $this->withSession([
                'participant_id' => $participant->id,
                'assessment_session_id' => $session->id,
            ])->putJson(route('api.answers.autosave'), [
                'display_order' => 1,
                'answer_value' => '4',
            ])->assertConflict();
        }
    }

    public function test_resume_increments_resume_count_and_opens_first_unanswered_question(): void
    {
        $participant = $this->startedParticipant();
        $firstQuestion = Question::query()->where('display_order', 1)->firstOrFail();

        Answer::create([
            'participant_id' => $participant->id,
            'question_id' => $firstQuestion->id,
            'answer_value' => '4',
            'saved_at' => now(),
        ]);

        $this->withHeader('X-SL-Device-ID', 'resume-device')
            ->post(route('code.validate'), [
                'access_code' => 'CFA-FLOW-CODE',
            ])->assertRedirect(route('assessment.questions.show', ['order' => 2]));

        $this->assertDatabaseHas('assessment_sessions', [
            'participant_id' => $participant->id,
            'resume_count' => 1,
            'is_writer' => true,
        ]);
    }

    public function test_question_page_increments_refresh_count(): void
    {
        $participant = $this->startedParticipant();
        $session = $this->assessmentSession($participant);

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
            'assessment_session_id' => $session->id,
        ]);

        $this->get(route('assessment.questions.show', ['order' => 1]))->assertOk();
        $this->get(route('assessment.questions.show', ['order' => 1]))->assertOk();

        $this->assertSame(2, $session->fresh()->refresh_count);
    }

    public function test_third_device_locks_access_code(): void
    {
        $participant = $this->startedParticipant();

        $this->assessmentSession($participant, 'device-one');
        $this->assessmentSession($participant, 'device-two');

        $this->withHeader('X-SL-Device-ID', 'device-three')
            ->post(route('code.validate'), [
                'access_code' => 'CFA-FLOW-CODE',
            ])->assertRedirect(route('landing'));

        $this->assertDatabaseHas('access_codes', [
            'id' => $participant->access_code_id,
            'status' => AccessCode::STATUS_LOCKED,
            'locked_reason' => 'Device limit exceeded during participant assessment.',
        ]);
    }

    public function test_question_page_contains_autosave_shell_without_internal_metadata(): void
    {
        $participant = $this->startedParticipant();
        $session = $this->assessmentSession($participant);

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
            'assessment_session_id' => $session->id,
        ])->get(route('assessment.questions.show', ['order' => 1]))
            ->assertOk()
            ->assertSee('autosave-status')
            ->assertSee('Tersimpan')
            ->assertSee('localStorage')
            ->assertDontSee('category')
            ->assertDontSee('scoring_direction')
            ->assertDontSee('scoring_map')
            ->assertDontSee('red_flag_options')
            ->assertDontSee('consistency_pair');
    }

    public function test_final_submit_is_idempotent_after_completion(): void
    {
        $participant = $this->startedParticipant();
        $this->completeAllAnswers($participant);

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ])->post(route('assessment.submit'), [
            'final_confirmation' => '1',
            'submission_attempt_id' => 'attempt-one',
        ])->assertRedirect(route('assessment.completion'));

        $this->assertDatabaseHas('access_codes', [
            'id' => $participant->access_code_id,
            'status' => AccessCode::STATUS_COMPLETED,
            'submission_attempt_id' => 'attempt-one',
        ]);

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ])->post(route('assessment.submit'), [
            'final_confirmation' => '1',
            'submission_attempt_id' => 'attempt-two',
        ])->assertRedirect(route('assessment.completion'));

        $this->assertDatabaseHas('access_codes', [
            'id' => $participant->access_code_id,
            'submission_attempt_id' => 'attempt-one',
        ]);
    }

    private function startedParticipant(
        string $status = AccessCode::STATUS_IN_PROGRESS,
        string $displayCode = 'CFA-FLOW-CODE',
    ): Participant {
        $this->seed(QuestionBankSeeder::class);

        $accessCode = $this->createAccessCode($displayCode, $status);

        return Participant::create([
            'access_code_id' => $accessCode->id,
            'display_name' => 'Naya',
            'discord_username' => '@naya',
        ]);
    }

    private function assessmentSession(Participant $participant, string $deviceId = 'phase-four-device'): AssessmentSession
    {
        return AssessmentSession::create([
            'participant_id' => $participant->id,
            'session_token_hash' => hash('sha256', $deviceId),
            'device_id' => $deviceId,
            'user_agent' => 'Feature test browser',
            'started_at' => now(),
            'last_seen_at' => now(),
            'is_writer' => true,
        ]);
    }

    private function createAccessCode(string $displayCode, string $status): AccessCode
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', trim($displayCode)));

        return AccessCode::create([
            'code_hash' => hash('sha256', $normalized),
            'display_code' => $normalized,
            'status' => $status,
        ]);
    }

    private function completeAllAnswers(Participant $participant): void
    {
        Question::query()
            ->where('is_active', true)
            ->get()
            ->each(function (Question $question) use ($participant): void {
                Answer::create([
                    'participant_id' => $participant->id,
                    'question_id' => $question->id,
                    'answer_value' => $question->question_type === 'situational' ? 'A' : '4',
                    'saved_at' => now(),
                ]);
            });
    }
}
