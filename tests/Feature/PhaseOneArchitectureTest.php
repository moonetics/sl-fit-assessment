<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Admin;
use App\Models\AdminNote;
use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\AuditLog;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Result;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class PhaseOneArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_core_tables_are_available(): void
    {
        foreach ([
            'admins',
            'access_codes',
            'participants',
            'questions',
            'answers',
            'assessment_sessions',
            'results',
            'admin_notes',
            'audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }
    }

    public function test_access_code_statuses_are_limited_by_the_model(): void
    {
        $this->assertSame([
            'Unused',
            'In Progress',
            'Completed',
            'Expired',
            'Locked',
        ], AccessCode::STATUSES);

        $this->expectException(InvalidArgumentException::class);

        AccessCode::create([
            'code_hash' => 'bad-status-hash',
            'status' => 'Paused',
        ]);
    }

    public function test_question_bank_seeder_creates_ninety_six_active_questions(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $this->assertSame(96, Question::query()->where('is_active', true)->count());
        $this->assertSame(12, Question::query()->where('question_type', 'situational')->count());
        $this->assertSame(11, Question::query()->where('is_consistency_item', true)->count());
        $this->assertSame(26, Question::query()->whereNotNull('profile_axis')->count());
        $this->assertNotNull(Question::query()->where('question_number', 46)->value('options'));
    }

    public function test_model_relationships_can_be_traversed(): void
    {
        $admin = Admin::create([
            'email' => 'owner@squadlimpul.test',
            'role' => 'owner',
        ]);

        $accessCode = AccessCode::create([
            'code_hash' => 'hashed-code-1',
            'display_code' => 'CFA-TEST-CODE',
            'created_by' => $admin->id,
        ]);

        $participant = Participant::create([
            'access_code_id' => $accessCode->id,
            'display_name' => 'RakaObby',
            'discord_username' => '@raka_speed',
        ]);

        $question = Question::create([
            'question_number' => 999,
            'text' => 'Saya siap mengikuti aturan komunitas.',
            'question_type' => 'likert',
            'category' => 'Rule Acceptance',
            'scoring_direction' => 'normal',
        ]);

        Answer::create([
            'participant_id' => $participant->id,
            'question_id' => $question->id,
            'answer_value' => '4',
            'saved_at' => now(),
        ]);

        AssessmentSession::create([
            'participant_id' => $participant->id,
            'session_token_hash' => 'session-hash',
            'device_id' => 'device-1',
            'started_at' => now(),
            'last_seen_at' => now(),
        ]);

        Result::create([
            'participant_id' => $participant->id,
            'community_fit_score' => 84,
            'competitive_fit_score' => 79,
            'risk_level' => 'Low',
            'honesty_status' => 'Valid',
            'member_type' => 'Competitive Racer',
            'final_status' => 'Accepted',
        ]);

        AdminNote::create([
            'participant_id' => $participant->id,
            'admin_id' => $admin->id,
            'note' => 'Good baseline.',
        ]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'CODE_CREATED',
            'entity_type' => 'access_code',
            'entity_id' => $accessCode->id,
            'after_data' => ['status' => 'Unused'],
        ]);

        $this->assertTrue($admin->accessCodes->first()->is($accessCode));
        $this->assertTrue($accessCode->participant->is($participant));
        $this->assertTrue($participant->answers->first()->question->is($question));
        $this->assertTrue($participant->sessions->first()->is_active);
        $this->assertTrue($participant->result->is(Result::first()));
        $this->assertSame('Good baseline.', $participant->notes->first()->note);
        $this->assertSame('CODE_CREATED', $admin->auditLogs->first()->action);
    }

    public function test_phase_one_api_routes_are_registered(): void
    {
        foreach ([
            'api.code.validate',
            'api.assessment.start',
            'api.assessment.current',
            'api.answers.autosave',
            'api.assessment.submit',
            'api.assessment.completion',
            'api.admin.login',
            'api.admin.codes.store',
            'api.admin.codes.index',
            'api.admin.codes.reset',
            'api.admin.codes.lock',
            'api.admin.codes.unlock',
            'api.admin.participants.index',
            'api.admin.participants.result',
            'api.admin.participants.notes.store',
            'api.admin.participants.final-status.update',
            'api.admin.export.results',
            'api.admin.audit-logs.index',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route [{$routeName}].");
        }
    }
}
