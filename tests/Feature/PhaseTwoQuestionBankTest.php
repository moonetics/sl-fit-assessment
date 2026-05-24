<?php

namespace Tests\Feature;

use App\Models\Question;
use Database\Seeders\QuestionBankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PhaseTwoQuestionBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_two_question_metadata_columns_exist(): void
    {
        foreach ([
            'display_order',
            'public_options',
            'red_flag_options',
            'consistency_pair',
            'consistency_check',
            'admin_notes',
            'profile_axis',
            'profile_pole',
            'subcategory',
            'risk_tags',
            'consistency_pair_id',
            'research_basis',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('questions', $column), "Missing questions.{$column}.");
        }
    }

    public function test_question_bank_is_idempotent_and_has_complete_display_order(): void
    {
        $this->seed(QuestionBankSeeder::class);
        $this->seed(QuestionBankSeeder::class);

        $this->assertSame(96, Question::query()->where('is_active', true)->count());
        $this->assertSame(range(1, 96), Question::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->pluck('display_order')
            ->all());
    }

    public function test_participant_display_order_uses_hidden_placement_reference(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $expectedInternalOrder = [
            ...range(1, 8),
            54,
            ...range(9, 16),
            55,
            ...range(17, 24),
            56,
            ...range(25, 32),
            57,
            ...range(33, 40),
            58,
            ...range(41, 48),
            59,
            77,
            49,
            78,
            50,
            79,
            51,
            80,
            52,
            81,
            53,
            82,
            60,
            83,
            84,
            85,
            86,
            ...range(61, 76),
            ...range(87, 96),
        ];

        $this->assertSame($expectedInternalOrder, Question::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->pluck('question_number')
            ->all());
    }

    public function test_situational_questions_have_public_options_scoring_maps_and_red_flags(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $situationalQuestions = Question::query()
            ->where('question_type', 'situational')
            ->get();

        $this->assertCount(12, $situationalQuestions);

        foreach ($situationalQuestions as $question) {
            $this->assertSame(['A', 'B', 'C', 'D'], array_keys($question->public_options));
            $this->assertSame(['A', 'B', 'C', 'D'], array_keys($question->scoring_map));
            $this->assertSame(['D'], $question->red_flag_options);
        }
    }

    public function test_consistency_items_have_internal_metadata_only(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $this->assertSame(11, Question::query()->where('is_consistency_item', true)->count());

        $this->assertSame([56], Question::query()->where('question_number', 54)->firstOrFail()->consistency_pair);
        $this->assertSame('unrealistic_perfection_check', Question::query()->where('question_number', 55)->firstOrFail()->consistency_check);
        $this->assertSame('impossible_perfection_check', Question::query()->where('question_number', 58)->firstOrFail()->consistency_check);
        $this->assertSame([21, 40, 48], Question::query()->where('question_number', 59)->firstOrFail()->consistency_pair);
        $this->assertSame([21, 40, 48], Question::query()->where('question_number', 60)->firstOrFail()->consistency_pair);
        $this->assertSame('chat_tone_awareness', Question::query()->where('question_number', 83)->firstOrFail()->consistency_pair_id);
        $this->assertSame([85], Question::query()->where('question_number', 86)->firstOrFail()->consistency_pair);
    }

    public function test_profile_questions_have_internal_axis_metadata(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $this->assertSame(26, Question::query()->whereNotNull('profile_axis')->count());
        $this->assertSame(4, Question::query()->where('profile_axis', 'social')->count());
        $this->assertSame(4, Question::query()->where('profile_axis', 'play_drive')->count());
        $this->assertSame(4, Question::query()->where('profile_axis', 'rule_style')->count());
        $this->assertSame(4, Question::query()->where('profile_axis', 'conflict_style')->count());
        $this->assertSame(2, Question::query()->where('profile_axis', 'team_style')->count());
        $this->assertSame(2, Question::query()->where('profile_axis', 'competitive_style')->count());
        $this->assertSame(2, Question::query()->where('profile_axis', 'drama_resistance')->count());
        $this->assertSame(2, Question::query()->where('profile_axis', 'feedback_style')->count());
        $this->assertSame(2, Question::query()->where('profile_axis', 'interaction_style')->count());
        $this->assertSame('S', Question::query()->where('question_number', 61)->firstOrFail()->profile_pole);
        $this->assertSame('T', Question::query()->where('question_number', 96)->firstOrFail()->profile_pole);
    }

    public function test_participant_payload_does_not_expose_internal_metadata(): void
    {
        $this->seed(QuestionBankSeeder::class);

        $payload = Question::activeForParticipant()
            ->get()
            ->map(fn (Question $question): array => $question->toParticipantPayload())
            ->first();

        $this->assertSame([
            'display_order',
            'text',
            'question_type',
            'public_options',
        ], array_keys($payload));

        foreach ([
            'category',
            'subcategory',
            'scoring_direction',
            'scoring_map',
            'red_flag_options',
            'risk_tags',
            'consistency_pair',
            'consistency_pair_id',
            'consistency_check',
            'is_consistency_item',
            'admin_notes',
            'research_basis',
            'profile_axis',
            'profile_pole',
        ] as $internalField) {
            $this->assertArrayNotHasKey($internalField, $payload);
        }
    }
}
