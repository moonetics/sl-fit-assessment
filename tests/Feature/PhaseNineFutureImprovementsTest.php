<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Admin;
use App\Models\Answer;
use App\Models\AssessmentSetting;
use App\Models\AuditLog;
use App\Models\CodeBatch;
use App\Models\Participant;
use App\Models\Question;
use App\Services\AssessmentScoringService;
use App\Services\QuestionOrderService;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseNineFutureImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_batch_generate_unique_codes_and_filter_dashboard_by_batch(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.codes.batch-store'), [
            'name' => 'May Recruitment',
            'source' => 'Discord event',
            'participants_csv' => "Naya Limpul, 111111111111111111\nRaka Speed, 222222222222222222\nMika Quiet, 333333333333333333",
        ])->assertRedirect(route('admin.batches.index'));

        $batch = CodeBatch::query()->firstOrFail();
        $hashes = AccessCode::query()->where('code_batch_id', $batch->id)->pluck('code_hash');

        $this->assertSame(3, $hashes->count());
        $this->assertSame(3, $hashes->unique()->count());
        $this->assertTrue(AccessCode::query()->where('code_batch_id', $batch->id)->get()->every(
            fn (AccessCode $code): bool => str_starts_with($code->display_code, 'SLFA-')
        ));
        $this->assertDatabaseHas('access_codes', ['assigned_name' => 'Naya Limpul']);
        $this->assertDatabaseHas('access_codes', [
            'assigned_name' => 'Raka Speed',
            'assigned_discord_id' => '222222222222222222',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'BATCH_CODES_GENERATED']);

        $this->get(route('admin.dashboard', ['batch_id' => $batch->id]))
            ->assertOk()
            ->assertSee('May Recruitment')
            ->assertSee('Discord event');

        $this->get(route('admin.batches.index'))
            ->assertOk()
            ->assertSee('Naya Limpul')
            ->assertSee('111111111111111111')
            ->assertSee('SLFA-')
            ->assertSee('Copy all')
            ->assertSee(rawurlencode(' - Naya Limpul (111111111111111111)'), false);
    }

    public function test_batch_generate_rejects_missing_discord_user_id_rows(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.codes.batch-store'), [
            'name' => 'Broken Batch',
            'participants_csv' => "Naya Limpul, 111111111111111111\nRaka Speed",
        ])->assertSessionHasErrors(['participants_csv']);

        $this->assertSame(0, AccessCode::query()->count());
    }

    public function test_admin_can_update_scoring_thresholds_and_scorer_uses_database_setting(): void
    {
        $this->actingAsAdmin();

        $this->patch(route('admin.scoring-settings.update'), [
            'min_duration_minutes' => 1,
            'high_speed_minutes' => 1,
            'straight_lining_medium' => 0.98,
            'straight_lining_high' => 0.99,
            'perfection_medium' => 0.98,
            'perfection_high' => 0.99,
            'refresh_count' => 99,
            'resume_count' => 99,
            'device_count' => 2,
            'min_answer_seconds' => 0,
            'fast_answer_count' => 60,
            'visibility_change_count' => 99,
            'offline_sync_count' => 99,
        ])->assertRedirect();

        $this->assertDatabaseHas('assessment_settings', ['key' => 'scoring_thresholds']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'SCORING_SETTINGS_UPDATED']);

        $participant = $this->participantWithHealthyAnswers(startedMinutesAgo: 5);
        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('Valid', $result->honesty_status);
        $this->assertNotContains('speed', collect($result->suspicious_flags ?? [])->pluck('type')->all());
    }

    public function test_report_routes_are_admin_only_and_markdown_renders_summary(): void
    {
        $participant = $this->participantWithHealthyAnswers();
        app(AssessmentScoringService::class)->score($participant);

        $this->get(route('admin.participants.report', $participant))
            ->assertRedirect(route('admin.login'));

        $this->actingAsAdmin();

        $this->get(route('admin.participants.report', $participant))
            ->assertOk()
            ->assertSee('PDF-ready report')
            ->assertSee('Assessment Result Summary')
            ->assertSee('SL Profile');

        $this->get(route('admin.participants.report.markdown', $participant))
            ->assertOk()
            ->assertSee('## Assessment Result Summary')
            ->assertSee('Community Fit Score')
            ->assertSee('SL Profile Code')
            ->assertSee('SL Profile Description')
            ->assertSee('SL Profile Best Fit')
            ->assertSee('Risk Reasons');
    }

    public function test_interview_module_stores_history_and_audit_log(): void
    {
        $this->actingAsAdmin();
        $participant = $this->participantWithHealthyAnswers();

        $this->post(route('admin.participants.interviews.store', $participant), [
            'interviewer_name' => 'Sera',
            'interview_at' => now()->format('Y-m-d\TH:i'),
            'questions_summary' => 'Discussed conflict handling and trial expectations.',
            'answers_summary' => 'Participant answered calmly and accepted onboarding rules.',
            'outcome' => 'Trial Recommended',
        ])->assertRedirect();

        $this->assertDatabaseHas('interviews', [
            'participant_id' => $participant->id,
            'interviewer_name' => 'Sera',
            'outcome' => 'Trial Recommended',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'INTERVIEW_CREATED']);

        $this->get(route('admin.participants.show', $participant))
            ->assertOk()
            ->assertSee('Interview history')
            ->assertSee('Trial Recommended');
    }

    public function test_participant_gets_stable_random_question_order_snapshot(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $participant = Participant::create([
            'access_code_id' => AccessCode::create([
                'code_hash' => hash('sha256', 'CFA-RANDOM-ORDER'),
                'display_code' => 'CFA-RANDOM-ORDER',
                'status' => AccessCode::STATUS_IN_PROGRESS,
            ])->id,
            'display_name' => 'Mika',
            'discord_username' => '@mika',
        ]);

        $service = app(QuestionOrderService::class);
        $snapshot = $service->ensureSnapshot($participant);
        $snapshotAgain = $service->ensureSnapshot($participant->fresh());
        $canonical = Question::query()->where('is_active', true)->orderBy('display_order')->pluck('question_number')->all();

        $this->assertCount(76, $snapshot);
        $this->assertSame($snapshot, $snapshotAgain);
        $this->assertSame(range(1, 76), collect($snapshot)->sort()->values()->all());
        $this->assertNotSame($canonical, $snapshot);
        $this->assertSame(1, $service->questionForOrder($participant->fresh(), 1)?->question_number);
    }

    public function test_existing_sixty_question_snapshot_is_extended_with_profile_questions(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $participant = Participant::create([
            'access_code_id' => AccessCode::create([
                'code_hash' => hash('sha256', 'CFA-OLD-SNAPSHOT'),
                'display_code' => 'CFA-OLD-SNAPSHOT',
                'status' => AccessCode::STATUS_IN_PROGRESS,
            ])->id,
            'display_name' => 'Legacy Snapshot',
            'discord_username' => '@legacy',
            'question_order_snapshot' => range(1, 60),
        ]);

        $snapshot = app(QuestionOrderService::class)->ensureSnapshot($participant);

        $this->assertCount(76, $snapshot);
        $this->assertSame(range(1, 60), array_slice($snapshot, 0, 60));
        $this->assertSame(range(61, 76), array_slice($snapshot, 60));
    }

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::create([
            'email' => 'admin@squadlimpul.local',
            'password_hash' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['admin_id' => $admin->id]);

        return $admin;
    }

    private function participantWithHealthyAnswers(int $startedMinutesAgo = 20): Participant
    {
        $this->seed(QuestionBankSeeder::class);

        $accessCode = AccessCode::create([
            'code_hash' => hash('sha256', 'CFA-PHASE-NINE'),
            'display_code' => 'CFA-PHASE-NINE',
            'status' => AccessCode::STATUS_IN_PROGRESS,
            'started_at' => now()->subMinutes($startedMinutesAgo),
            'completed_at' => now(),
        ]);

        $participant = Participant::create([
            'access_code_id' => $accessCode->id,
            'display_name' => 'Naya',
            'discord_username' => '@naya',
        ]);

        Question::query()->where('is_active', true)->get()->each(function (Question $question) use ($participant): void {
            Answer::create([
                'participant_id' => $participant->id,
                'question_id' => $question->id,
                'answer_value' => $this->healthyAnswer($question),
                'saved_at' => now(),
                'client_duration_seconds' => 8,
            ]);
        });

        return $participant->fresh(['accessCode', 'sessions']);
    }

    private function healthyAnswer(Question $question): string
    {
        if (in_array($question->question_number, [6, 13, 19, 25, 33, 41], true)) {
            return '3';
        }

        return match (true) {
            $question->question_type === 'situational' => 'A',
            $question->scoring_direction === 'reverse' => '1',
            $question->scoring_direction === 'reverse_soft' => '2',
            $question->scoring_direction === 'normal_soft' => '3',
            default => '4',
        };
    }
}
