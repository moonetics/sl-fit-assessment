<?php

namespace App\Services;

use App\Models\AccessCode;
use App\Models\Answer;
use App\Models\Participant;
use App\Models\Question;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AnswerRecorder
{
    public function record(
        Participant $participant,
        Question $question,
        string $answerValue,
        ?Carbon $clientSavedAt = null,
        string $syncStatus = 'saved',
        ?Carbon $answerStartedAt = null,
        ?int $clientDurationSeconds = null,
        int $visibilityChangeCount = 0,
        int $offlineSyncCount = 0,
    ): Answer {
        $accessCode = $participant->accessCode;

        if (! $accessCode || in_array($accessCode->status, [
            AccessCode::STATUS_COMPLETED,
            AccessCode::STATUS_EXPIRED,
            AccessCode::STATUS_LOCKED,
        ], true)) {
            throw ValidationException::withMessages([
                'answer_value' => 'Assessment tidak tersedia untuk menyimpan jawaban.',
            ]);
        }

        $allowedAnswers = array_map('strval', array_keys($question->public_options ?? []));

        if (! in_array($answerValue, $allowedAnswers, true)) {
            throw ValidationException::withMessages([
                'answer_value' => 'Jawaban tidak valid untuk soal ini.',
            ]);
        }

        $answer = Answer::query()->firstOrNew([
            'participant_id' => $participant->id,
            'question_id' => $question->id,
        ]);

        $answer->answer_value = $answerValue;
        $answer->revision = $answer->exists ? $answer->revision + 1 : 1;
        $answer->saved_at = now();
        $answer->client_saved_at = $clientSavedAt;
        $answer->answer_started_at = $answerStartedAt;
        $answer->client_duration_seconds = $clientDurationSeconds;
        $answer->visibility_change_count = $visibilityChangeCount;
        $answer->offline_sync_count = $offlineSyncCount;
        $answer->sync_status = $syncStatus;
        $answer->save();

        return $answer;
    }
}
