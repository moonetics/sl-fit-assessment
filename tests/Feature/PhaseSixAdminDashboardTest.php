<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Result;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseSixAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_login_and_logout(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('admin_id', $admin->id);

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionMissing('admin_id');
    }

    public function test_admin_routes_require_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.questions.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_cannot_generate_code_without_required_participant_identity(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.codes.store'))
            ->assertSessionHasErrors(['assigned_name', 'assigned_discord_id']);
    }

    public function test_admin_can_generate_code(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.codes.store'), [
            'assigned_name' => 'Raka Limpul',
            'assigned_discord_id' => '123456789012345678',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $code = AccessCode::query()->first();

        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^SLFA-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/', $code->display_code);
        $this->assertSame('Raka Limpul', $code->assigned_name);
        $this->assertSame('123456789012345678', $code->assigned_discord_id);
        $this->assertSame(AccessCode::STATUS_UNUSED, $code->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CODE_GENERATED']);
    }

    public function test_admin_dashboard_shows_assigned_identity(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.codes.store'), [
            'assigned_name' => 'Raka Limpul',
            'assigned_discord_id' => '987654321098765432',
        ])->assertRedirect(route('admin.dashboard'));

        $code = AccessCode::query()->firstOrFail();

        $this->assertStringStartsWith('SLFA-', $code->display_code);
        $this->assertSame('Raka Limpul', $code->assigned_name);
        $this->assertSame('987654321098765432', $code->assigned_discord_id);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Raka Limpul')
            ->assertSee('Discord ID: 987654321098765432');
    }

    public function test_admin_can_view_read_only_question_bank_with_metadata(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $this->actingAsAdmin();

        $this->get(route('admin.questions.index'))
            ->assertOk()
            ->assertSee('Assessment Questions')
            ->assertSee('Active Questions')
            ->assertSee('76')
            ->assertSee('Community Fit')
            ->assertSee('Situational')
            ->assertSee('Honesty Check')
            ->assertSee('SL Profile')
            ->assertSee('Saya menjaga bahasa chat')
            ->assertSee('Online Behavior')
            ->assertSee('normal')
            ->assertSee('Red flag only');

        $this->assertSame(76, Question::query()->where('is_active', true)->count());
    }

    public function test_admin_question_bank_filters_by_search_type_profile_consistency_and_red_flags(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $this->actingAsAdmin();

        $this->get(route('admin.questions.index', ['q' => 'Leaderboard']))
            ->assertOk()
            ->assertSee('Leaderboard dan improve time')
            ->assertDontSee('Saya menjaga bahasa chat');

        $this->get(route('admin.questions.index', ['question_type' => 'situational']))
            ->assertOk()
            ->assertSee('Menuduh pemenang beruntung')
            ->assertDontSee('Leaderboard dan improve time');

        $this->get(route('admin.questions.index', ['profile_axis' => 'social']))
            ->assertOk()
            ->assertSee('memulai obrolan ringan')
            ->assertSee('social / S')
            ->assertDontSee('Leaderboard dan improve time');

        $this->get(route('admin.questions.index', ['consistency_only' => '1']))
            ->assertOk()
            ->assertSee('extreme_perfection_check')
            ->assertDontSee('Leaderboard dan improve time');

        $this->get(route('admin.questions.index', ['red_flag_only' => '1']))
            ->assertOk()
            ->assertSee('Menuduh pemenang beruntung')
            ->assertSee('D')
            ->assertDontSee('Saya menjaga bahasa chat');
    }

    public function test_admin_can_view_result_detail_and_add_note(): void
    {
        $this->actingAsAdmin();
        $participant = $this->participantWithResult();

        $this->get(route('admin.participants.show', $participant))
            ->assertOk()
            ->assertSee('Community Fit')
            ->assertSee('Red flags')
            ->assertSee('Answer review');

        $this->post(route('admin.participants.notes.store', $participant), [
            'note' => 'Needs onboarding follow-up.',
        ])->assertRedirect();

        $this->assertDatabaseHas('admin_notes', [
            'participant_id' => $participant->id,
            'note' => 'Needs onboarding follow-up.',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ADMIN_NOTE_CREATED']);
    }

    public function test_final_status_override_requires_reason_and_writes_audit_log(): void
    {
        $this->actingAsAdmin();
        $participant = $this->participantWithResult();

        $this->patch(route('admin.participants.final-status.update', $participant), [
            'final_status' => 'Accepted with Trial',
        ])->assertSessionHasErrors(['override_reason']);

        $this->patch(route('admin.participants.final-status.update', $participant), [
            'final_status' => 'Accepted with Trial',
            'override_reason' => 'Admin interview looked healthy.',
        ])->assertRedirect();

        $this->assertDatabaseHas('results', [
            'participant_id' => $participant->id,
            'final_status' => 'Accepted with Trial',
            'auto_final_status' => 'Manual Review',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'FINAL_STATUS_OVERRIDDEN']);
    }

    public function test_lock_unlock_and_reset_write_audit_logs(): void
    {
        $this->actingAsAdmin();
        $code = AccessCode::create([
            'code_hash' => hash('sha256', 'CFA-ADMIN-TEST'),
            'display_code' => 'CFA-ADMIN-TEST',
            'status' => AccessCode::STATUS_IN_PROGRESS,
        ]);

        $this->patch(route('admin.codes.lock', $code), [
            'reason' => 'Suspicious device activity.',
        ])->assertRedirect();
        $this->assertSame(AccessCode::STATUS_LOCKED, $code->fresh()->status);

        $this->patch(route('admin.codes.unlock', $code), [
            'status' => AccessCode::STATUS_IN_PROGRESS,
            'reason' => 'Reviewed by admin.',
        ])->assertRedirect();
        $this->assertSame(AccessCode::STATUS_IN_PROGRESS, $code->fresh()->status);

        $this->patch(route('admin.codes.reset', $code), [
            'reason' => 'Participant needs restart.',
        ])->assertRedirect();
        $this->assertSame(AccessCode::STATUS_UNUSED, $code->fresh()->status);

        $this->assertSame(1, AuditLog::query()->where('action', 'CODE_LOCKED')->count());
        $this->assertSame(1, AuditLog::query()->where('action', 'CODE_UNLOCKED')->count());
        $this->assertSame(1, AuditLog::query()->where('action', 'CODE_RESET')->count());
    }

    private function actingAsAdmin(): Admin
    {
        $admin = $this->admin();
        $this->withSession(['admin_id' => $admin->id]);

        return $admin;
    }

    private function admin(): Admin
    {
        return Admin::query()->firstOrCreate(
            ['email' => 'admin@squadlimpul.local'],
            [
                'password_hash' => Hash::make('password'),
                'role' => 'owner',
            ],
        );
    }

    private function participantWithResult(): Participant
    {
        $code = AccessCode::create([
            'code_hash' => hash('sha256', 'CFA-RESULT-TEST'),
            'display_code' => 'CFA-RESULT-TEST',
            'status' => AccessCode::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $participant = Participant::create([
            'access_code_id' => $code->id,
            'display_name' => 'Naya',
            'discord_username' => '@naya',
        ]);

        Result::create([
            'participant_id' => $participant->id,
            'community_fit_score' => 76,
            'competitive_fit_score' => 42,
            'risk_score' => 28,
            'risk_level' => 'Low',
            'honesty_status' => 'Valid',
            'member_type' => 'Casual Community Member',
            'final_status' => 'Manual Review',
            'auto_final_status' => 'Manual Review',
            'category_scores' => ['Online Behavior' => ['score' => 80]],
            'red_flags' => [],
            'suspicious_flags' => [],
            'generated_at' => now(),
        ]);

        return $participant;
    }
}
