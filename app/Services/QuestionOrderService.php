<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Question;

class QuestionOrderService
{
    /**
     * @return array<int, int>
     */
    public function ensureSnapshot(Participant $participant): array
    {
        $activeQuestionNumbers = Question::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->pluck('question_number')
            ->map(fn (int $number): int => $number)
            ->all();

        if (is_array($participant->question_order_snapshot) && count($participant->question_order_snapshot) > 0) {
            $snapshot = array_values(array_map('intval', $participant->question_order_snapshot));
            $activeLookup = array_flip($activeQuestionNumbers);
            $snapshot = array_values(array_filter(
                $snapshot,
                fn (int $questionNumber): bool => isset($activeLookup[$questionNumber]),
            ));
            $missing = array_values(array_diff($activeQuestionNumbers, $snapshot));
            $normalizedSnapshot = [...$snapshot, ...$missing];

            if ($normalizedSnapshot !== array_values($participant->question_order_snapshot)) {
                $participant->forceFill([
                    'question_order_snapshot' => $normalizedSnapshot,
                ])->save();
            }

            return $normalizedSnapshot;
        }

        $questionNumbers = $activeQuestionNumbers;
        $firstQuestion = array_shift($questionNumbers);
        $seed = crc32($participant->id.'|'.$participant->access_code_id);
        usort($questionNumbers, function (int $a, int $b) use ($seed): int {
            return strcmp(
                hash('sha256', $seed.'|'.$a),
                hash('sha256', $seed.'|'.$b),
            );
        });

        if ($firstQuestion !== null) {
            array_unshift($questionNumbers, $firstQuestion);
        }

        $participant->forceFill([
            'question_order_snapshot' => $questionNumbers,
        ])->save();

        return $questionNumbers;
    }

    public function questionForOrder(Participant $participant, int $order): ?Question
    {
        $snapshot = $this->ensureSnapshot($participant);
        $questionNumber = $snapshot[$order - 1] ?? null;

        if (! $questionNumber) {
            return null;
        }

        return Question::query()
            ->where('is_active', true)
            ->where('question_number', $questionNumber)
            ->first();
    }

    public function orderForQuestion(Participant $participant, string $questionId): ?int
    {
        $snapshot = $this->ensureSnapshot($participant);
        $questionNumber = Question::query()->whereKey($questionId)->value('question_number');

        if (! $questionNumber) {
            return null;
        }

        $index = array_search((int) $questionNumber, $snapshot, true);

        return $index === false ? null : $index + 1;
    }

    public function total(Participant $participant): int
    {
        return count($this->ensureSnapshot($participant));
    }
}
