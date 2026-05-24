<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Answer;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Result;
use App\Services\AssessmentScoringService;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhaseFiveScoringEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoring_calculates_normal_reverse_situational_and_fit_scores(): void
    {
        $participant = $this->participantWithAnswers();

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertGreaterThanOrEqual(90, $result->community_fit_score);
        $this->assertGreaterThanOrEqual(90, $result->competitive_fit_score);
        $this->assertLessThan(20, $result->risk_score);
        $this->assertSame('Very Low', $result->risk_level);
        $this->assertSame('Valid', $result->honesty_status);
        $this->assertSame('Competitive Racer', $result->member_type);
        $this->assertSame('Accepted', $result->final_status);
        $this->assertSame('QCAC', $result->profile_code);
        $this->assertSame('Steady Supporter', $result->profile_name);
        $this->assertNotEmpty($result->profile_breakdown);
        $this->assertArrayHasKey('_profile', $result->profile_breakdown);
        $this->assertArrayHasKey('description', $result->profile_breakdown['_profile']);
        $this->assertArrayHasKey('admin_guidance', $result->profile_breakdown['_profile']);
        $this->assertArrayHasKey('confidence', $result->profile_breakdown['social']);
        $this->assertArrayHasKey('watchouts', $result->profile_breakdown['social']);
        $this->assertNotEmpty($result->risk_reasons);

        $this->assertDatabaseHas('answers', [
            'participant_id' => $participant->id,
            'question_id' => Question::where('question_number', 1)->value('id'),
            'answer_value' => '4',
            'score_value' => 4,
        ]);
        $this->assertDatabaseHas('answers', [
            'participant_id' => $participant->id,
            'question_id' => Question::where('question_number', 2)->value('id'),
            'answer_value' => '1',
            'score_value' => 4,
        ]);
        $this->assertDatabaseHas('answers', [
            'participant_id' => $participant->id,
            'question_id' => Question::where('question_number', 46)->value('id'),
            'answer_value' => 'A',
            'score_value' => 4,
        ]);
    }

    public function test_low_competitive_fit_can_be_accepted_as_casual_member(): void
    {
        $participant = $this->participantWithAnswers([
            7 => '3',
            9 => '1',
            10 => '4',
            29 => '1',
            30 => '4',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertGreaterThanOrEqual(90, $result->community_fit_score);
        $this->assertLessThan(55, $result->competitive_fit_score);
        $this->assertSame('Accepted as Casual Member', $result->final_status);
    }

    public function test_moderately_weak_risk_category_maps_to_low_risk(): void
    {
        $participant = $this->participantWithAnswers([
            5 => '4',
            6 => '1',
            13 => '1',
            23 => '1',
            24 => '4',
            45 => '1',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertGreaterThanOrEqual(20, $result->risk_score);
        $this->assertLessThan(35, $result->risk_score);
        $this->assertSame('Low', $result->risk_level);
    }

    public function test_two_medium_red_flags_make_medium_risk(): void
    {
        $participant = $this->participantWithAnswers([
            48 => 'C',
            49 => 'C',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('Medium', $result->risk_level);
        $this->assertSame('Manual Review', $result->final_status);
        $this->assertCount(2, $result->red_flags);
    }

    public function test_single_heavy_red_flag_makes_high_risk_and_never_accepted(): void
    {
        $participant = $this->participantWithAnswers([
            46 => 'D',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('High', $result->risk_level);
        $this->assertSame('Watchlist', $result->final_status);
        $this->assertNotSame('Accepted', $result->final_status);
        $this->assertCount(1, $result->red_flags);
    }

    public function test_multiple_heavy_red_flags_make_critical_risk_and_rejected(): void
    {
        $participant = $this->participantWithAnswers([
            46 => 'D',
            47 => 'D',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('Critical', $result->risk_level);
        $this->assertSame('Rejected', $result->final_status);
        $this->assertNotSame('Accepted', $result->final_status);
        $this->assertCount(2, $result->red_flags);
        $this->assertNotEmpty($result->risk_reasons);
    }

    public function test_very_high_risk_score_makes_critical_without_heavy_red_flags(): void
    {
        $participant = $this->participantWithAnswers($this->severeRiskWithoutHeavyRedFlags());

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertGreaterThanOrEqual(80, $result->risk_score);
        $this->assertSame('Critical', $result->risk_level);
        $this->assertSame('Watchlist', $result->final_status);
        $this->assertCount(0, array_filter($result->red_flags, fn (array $flag): bool => $flag['severity'] === 'heavy'));
    }

    public function test_profile_code_is_generated_separately_from_final_decision(): void
    {
        $participant = $this->participantWithAnswers([
            61 => '4',
            62 => '1',
            63 => '4',
            64 => '1',
            65 => '4',
            66 => '1',
            67 => '4',
            68 => '1',
            69 => '4',
            70 => '1',
            71 => '4',
            72 => '1',
            73 => '4',
            74 => '1',
            75 => '4',
            76 => '1',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('SRAC', $result->profile_code);
        $this->assertSame('Composed Race Captain', $result->profile_name);
        $this->assertSame('Accepted', $result->final_status);
        $this->assertArrayHasKey('social', $result->profile_breakdown);
        $this->assertSame('High', $result->profile_breakdown['social']['confidence']);
        $this->assertStringContainsString('Aktif', $result->profile_breakdown['social']['summary']);
    }

    public function test_questionable_honesty_forces_manual_review(): void
    {
        $participant = $this->participantWithAnswers([
            54 => '4',
            56 => '1',
            59 => '1',
            21 => '4',
        ]);

        $result = app(AssessmentScoringService::class)->score($participant);

        $this->assertSame('Questionable', $result->honesty_status);
        $this->assertSame('Manual Review', $result->final_status);
    }

    public function test_final_submit_creates_only_one_result(): void
    {
        $participant = $this->participantWithAnswers();

        $this->withSession([
            'access_code_id' => $participant->access_code_id,
            'participant_id' => $participant->id,
        ]);

        $payload = [
            'final_confirmation' => '1',
            'submission_attempt_id' => 'phase-five-submit',
        ];

        $this->post(route('assessment.submit'), $payload)
            ->assertRedirect(route('assessment.completion'));

        $this->post(route('assessment.submit'), $payload)
            ->assertRedirect(route('assessment.completion'));

        $this->assertSame(1, Result::query()->where('participant_id', $participant->id)->count());
        $this->assertDatabaseHas('access_codes', [
            'id' => $participant->access_code_id,
            'status' => AccessCode::STATUS_COMPLETED,
        ]);
    }

    /**
     * @param  array<int, string>  $overrides
     */
    private function participantWithAnswers(array $overrides = []): Participant
    {
        $this->seed(QuestionBankSeeder::class);

        $displayCode = 'CFA-SCORING-'.Str::uuid();

        $accessCode = AccessCode::create([
            'code_hash' => hash('sha256', $displayCode),
            'display_code' => $displayCode,
            'status' => AccessCode::STATUS_IN_PROGRESS,
            'started_at' => now()->subMinutes(20),
        ]);

        $participant = Participant::create([
            'access_code_id' => $accessCode->id,
            'display_name' => 'RakaObby',
            'discord_username' => '@raka',
        ]);

        Question::query()->where('is_active', true)->get()->each(function (Question $question) use ($participant, $overrides): void {
            Answer::create([
                'participant_id' => $participant->id,
                'question_id' => $question->id,
                'answer_value' => $overrides[$question->question_number] ?? $this->healthyAnswer($question),
                'saved_at' => now(),
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

    /**
     * @return array<int, string>
     */
    private function severeRiskWithoutHeavyRedFlags(): array
    {
        return [
            3 => '1',
            4 => '1',
            5 => '4',
            6 => '1',
            13 => '1',
            14 => '4',
            15 => '1',
            16 => '4',
            17 => '1',
            18 => '4',
            21 => '1',
            22 => '4',
            23 => '1',
            24 => '4',
            33 => '1',
            34 => '4',
            35 => '1',
            36 => '4',
            40 => '4',
            41 => '1',
            42 => '4',
            44 => '4',
            45 => '1',
            46 => 'C',
            48 => 'C',
            49 => 'C',
            51 => 'C',
            52 => 'C',
            53 => 'C',
        ];
    }
}
