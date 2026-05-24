<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\Participant;
use App\Models\Question;
use App\Services\QuestionOrderService;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantMvpFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unused_code_can_enter_instructions_and_start_assessment(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $accessCode = $this->createAccessCode('CFA-TEST-CODE');
        $accessCode->update([
            'assigned_name' => 'RakaObby',
            'assigned_discord_id' => '123456789012345678',
        ]);

        $this->post(route('code.validate'), [
            'access_code' => ' cfa-test-code ',
        ])->assertRedirect(route('assessment.instructions'));

        $this->get(route('assessment.instructions'))
            ->assertOk()
            ->assertSee('Hai, RakaObby')
            ->assertSee('Assessment ini bukan psikotes klinis');

        $this->post(route('assessment.start'), [
        ])->assertRedirect(route('assessment.questions.show', ['order' => 1]));

        $this->assertDatabaseHas('access_codes', [
            'id' => $accessCode->id,
            'status' => AccessCode::STATUS_IN_PROGRESS,
        ]);
        $this->assertDatabaseHas('participants', [
            'access_code_id' => $accessCode->id,
            'display_name' => 'RakaObby',
            'discord_username' => '123456789012345678',
            'discord_user_id' => '123456789012345678',
        ]);
        $this->assertSame(1, AssessmentSession::query()->count());
    }

    public function test_assigned_name_code_welcomes_participant_and_uses_admin_name(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $accessCode = $this->createAccessCode('SLFA-NAME-TEST');
        $accessCode->update([
            'assigned_name' => 'Bimo Ariel',
            'assigned_discord_id' => '987654321098765432',
        ]);

        $this->post(route('code.validate'), [
            'access_code' => 'SLFA-NAME-TEST',
        ])->assertRedirect(route('assessment.instructions'));

        $this->get(route('assessment.instructions'))
            ->assertOk()
            ->assertSee('Hai, Bimo Ariel')
            ->assertSee('WELCOME TO SQUAD LIMPUL FIT ASSESSMENT')
            ->assertDontSee('Nama ini sudah disiapkan oleh admin')
            ->assertDontSee('Discord username')
            ->assertDontSee('name="display_name"', false);

        $this->post(route('assessment.start'))
            ->assertRedirect(route('assessment.questions.show', ['order' => 1]));

        $this->assertDatabaseHas('participants', [
            'access_code_id' => $accessCode->id,
            'display_name' => 'Bimo Ariel',
            'discord_username' => '987654321098765432',
            'discord_user_id' => '987654321098765432',
        ]);
    }

    public function test_legacy_code_without_assigned_identity_cannot_start(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $this->createAccessCode('CFA-LEGACY-CODE');

        $this->post(route('code.validate'), [
            'access_code' => 'CFA-LEGACY-CODE',
        ])->assertRedirect(route('assessment.instructions'));

        $this->post(route('assessment.start'))
            ->assertRedirect()
            ->assertSessionHasErrors(['access_code']);

        $this->assertStringContainsString('data peserta lengkap', session('errors')->first('access_code'));
    }

    public function test_completed_expired_and_locked_codes_are_rejected(): void
    {
        foreach ([
            AccessCode::STATUS_COMPLETED => 'sudah selesai',
            AccessCode::STATUS_EXPIRED => 'sudah expired',
            AccessCode::STATUS_LOCKED => 'terkunci',
        ] as $status => $message) {
            $this->createAccessCode("CFA-{$status}", $status);

            $this->post(route('code.validate'), [
                'access_code' => "CFA-{$status}",
            ])
                ->assertRedirect()
                ->assertSessionHasErrors(['access_code']);

            $this->assertStringContainsString($message, session('errors')->first('access_code'));
        }
    }

    public function test_question_page_renders_public_payload_only_and_saves_answers(): void
    {
        $participant = $this->startedParticipant();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

        $this->get(route('assessment.questions.show', ['order' => 1]))
            ->assertOk()
            ->assertSee('Pilih jawaban yang paling terasa cocok')
            ->assertSee('Sangat tidak setuju')
            ->assertSee('Lewati dulu')
            ->assertSee('role="progressbar"', false)
            ->assertDontSee('Soal 1 dari')
            ->assertDontSee('Navigasi soal')
            ->assertDontSee('question-nav-link', false)
            ->assertDontSee('Perkiraan waktu')
            ->assertDontSee('Online Behavior')
            ->assertDontSee('scoring_direction')
            ->assertDontSee('consistency_check');

        $this->post(route('assessment.questions.answer', ['order' => 1]), [
            'answer_value' => '4',
            'direction' => 'next',
        ])->assertRedirect(route('assessment.questions.show', ['order' => 2]));

        $this->assertDatabaseHas('answers', [
            'participant_id' => $participant->id,
            'answer_value' => '4',
        ]);

        $this->get(route('assessment.questions.show', ['order' => 2]))
            ->assertOk()
            ->assertDontSee('question-nav-link', false);
    }

    public function test_question_can_be_skipped_without_creating_answer_and_review_lists_missing_orders(): void
    {
        $participant = $this->startedParticipant();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

        $this->post(route('assessment.questions.answer', ['order' => 1]), [
            'direction' => 'skip',
        ])->assertRedirect(route('assessment.questions.show', ['order' => 2]));

        $this->assertSame(0, Answer::query()->where('participant_id', $participant->id)->count());

        $this->get(route('assessment.review'))
            ->assertOk()
            ->assertSee('Masih ada soal kosong')
            ->assertSee('Item kosong 1')
            ->assertDontSee('Total soal')
            ->assertSee(route('assessment.questions.show', ['order' => 1]), false);
    }

    public function test_question_with_glossary_term_shows_neutral_note(): void
    {
        $participant = $this->startedParticipant();
        $order = app(QuestionOrderService::class)->orderForQuestion(
            $participant,
            Question::query()->where('question_number', 24)->value('id'),
        );

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ])->get(route('assessment.questions.show', ['order' => $order]))
            ->assertOk()
            ->assertSee('Catatan istilah')
            ->assertSee('Trash talk', false)
            ->assertDontSee('red_flag_options')
            ->assertDontSee('scoring_direction');
    }

    public function test_glossary_term_does_not_match_inside_other_words(): void
    {
        $participant = $this->startedParticipant();
        $order = app(QuestionOrderService::class)->orderForQuestion(
            $participant,
            Question::query()->where('question_number', 16)->value('id'),
        );

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ])->get(route('assessment.questions.show', ['order' => $order]))
            ->assertOk()
            ->assertSee('Kalau banyak orang juga melanggar aturan')
            ->assertDontSee('Catatan istilah')
            ->assertDontSee('GG adalah singkatan');
    }

    public function test_glossary_term_is_detected_in_public_options(): void
    {
        $participant = $this->startedParticipant();
        $order = app(QuestionOrderService::class)->orderForQuestion(
            $participant,
            Question::query()->where('question_number', 46)->value('id'),
        );

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ])->get(route('assessment.questions.show', ['order' => $order]))
            ->assertOk()
            ->assertSee('GG, aku salah di akhir')
            ->assertSee('Catatan istilah')
            ->assertSee('GG adalah singkatan');
    }

    public function test_submit_with_missing_answers_redirects_to_first_missing_question(): void
    {
        $participant = $this->startedParticipant();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

        $this->post(route('assessment.submit'), [
            'final_confirmation' => '1',
        ])->assertRedirect(route('assessment.questions.show', ['order' => 1]));
    }

    public function test_smoothed_progress_is_monotonic_and_reaches_one_hundred_on_last_question(): void
    {
        $participant = $this->startedParticipant();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

        $previous = 0;

        foreach ([1, 12, 24, 48, 72, 95, 96] as $order) {
            $response = $this->get(route('assessment.questions.show', ['order' => $order]))
                ->assertOk();
            preg_match('/aria-valuenow="(\d+)"/', $response->getContent(), $matches);

            $progress = (int) ($matches[1] ?? 0);
            $this->assertGreaterThanOrEqual($previous, $progress);
            $previous = $progress;
        }

        $this->assertSame(100, $previous);
    }

    public function test_complete_assessment_sets_code_to_completed_and_blocks_reuse(): void
    {
        $participant = $this->startedParticipant();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

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

        $this->get(route('assessment.review'))
            ->assertOk()
            ->assertDontSee('Total soal')
            ->assertSee('Submit final');

        $this->post(route('assessment.submit'), [
            'final_confirmation' => '1',
            'submission_attempt_id' => 'phase-three-test-submit',
        ])->assertRedirect(route('assessment.completion'));

        $this->assertDatabaseHas('access_codes', [
            'id' => $participant->access_code_id,
            'status' => AccessCode::STATUS_COMPLETED,
        ]);

        $this->get(route('assessment.completion'))
            ->assertOk()
            ->assertSee('Assessment berhasil dikirim')
            ->assertDontSee('Community Fit Score');

        $this->post(route('code.validate'), [
            'access_code' => 'CFA-FLOW-CODE',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors(['access_code']);
    }

    private function startedParticipant(): Participant
    {
        $this->seed(QuestionBankSeeder::class);

        $accessCode = $this->createAccessCode('CFA-FLOW-CODE', AccessCode::STATUS_IN_PROGRESS);

        return Participant::create([
            'access_code_id' => $accessCode->id,
            'display_name' => 'Naya',
            'discord_username' => '@naya',
        ]);
    }

    private function createAccessCode(string $displayCode, string $status = AccessCode::STATUS_UNUSED): AccessCode
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', trim($displayCode)));

        return AccessCode::create([
            'code_hash' => hash('sha256', $normalized),
            'display_code' => $normalized,
            'status' => $status,
        ]);
    }
}
