<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentScoringService
{
    public function __construct(private readonly AssessmentSettingsService $settings)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(Participant $participant): array
    {
        return $this->calculate($participant);
    }

    public function score(Participant $participant): Result
    {
        return DB::transaction(function () use ($participant): Result {
            $payload = $this->calculate($participant);

            foreach ($payload['answer_scores'] as $answerId => $scoreValue) {
                Answer::query()
                    ->where('id', $answerId)
                    ->update(['score_value' => $scoreValue]);
            }

            return Result::query()->updateOrCreate(
                ['participant_id' => $participant->id],
                [
                    'community_fit_score' => $payload['community_fit_score'],
                    'competitive_fit_score' => $payload['competitive_fit_score'],
                    'risk_score' => $payload['risk_score'],
                    'risk_level' => $payload['risk_level'],
                    'honesty_status' => $payload['honesty_status'],
                    'member_type' => $payload['member_type'],
                    'final_status' => $payload['final_status'],
                    'auto_final_status' => $payload['final_status'],
                    'profile_code' => $payload['profile_code'],
                    'profile_name' => $payload['profile_name'],
                    'profile_breakdown' => $payload['profile_breakdown'],
                    'category_scores' => $payload['category_scores'],
                    'red_flags' => $payload['red_flags'],
                    'suspicious_flags' => $payload['suspicious_flags'],
                    'risk_reasons' => $payload['risk_reasons'],
                    'generated_at' => now(),
                ],
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function calculate(Participant $participant): array
    {
        $questions = Question::query()
            ->where('is_active', true)
            ->orderBy('question_number')
            ->get()
            ->keyBy('id');

        $answers = Answer::query()
            ->where('participant_id', $participant->id)
            ->with('question')
            ->get();

        if ($answers->count() < $questions->count()) {
            throw ValidationException::withMessages([
                'answers' => 'Semua soal harus terjawab sebelum scoring.',
            ]);
        }

        $answerByNumber = $answers->keyBy(fn (Answer $answer): int => $answer->question->question_number);
        $categoryBuckets = [];
        $profileBuckets = $this->emptyProfileBuckets();
        $answerScores = [];
        $redFlags = [];
        $scorableIdealCount = 0;
        $scorableCount = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $score = $this->scoreAnswer($question, $answer->answer_value);
            $answerScores[$answer->id] = $score;

            if ($question->profile_axis && $question->profile_pole) {
                $this->addProfileAnswer($profileBuckets, $question->profile_axis, $question->profile_pole, (int) $answer->answer_value);
            }

            if (! $question->is_consistency_item && ! $question->profile_axis) {
                $scorableCount++;
                $scorableIdealCount += $score === 4 ? 1 : 0;
            }

            if ($question->question_type === 'situational') {
                $redFlags = [
                    ...$redFlags,
                    ...$this->situationalFlags($question, $answer->answer_value, $score),
                ];
            }

            if ($question->profile_axis || $question->is_consistency_item || $question->category === 'Honesty & Consistency') {
                continue;
            }

            foreach ($this->categories($question->category) as $category) {
                $categoryBuckets[$category] ??= ['raw' => 0, 'min' => 0, 'max' => 0, 'items' => 0];
                $categoryBuckets[$category]['raw'] += $score;
                $categoryBuckets[$category]['min'] += $question->question_type === 'situational' ? 0 : 1;
                $categoryBuckets[$category]['max'] += 4;
                $categoryBuckets[$category]['items']++;
            }
        }

        $categoryScores = $this->categoryScores($categoryBuckets);
        $communityFit = $this->weightedScore($categoryScores, config('assessment_scoring.community_weights'));
        $competitiveFit = $this->weightedScore($categoryScores, config('assessment_scoring.competitive_weights'));
        $riskScore = $this->riskScore($categoryScores);
        $heavyRedFlags = $this->countFlags($redFlags, 'heavy');
        $mediumRedFlags = $this->countFlags($redFlags, 'medium');
        $riskLevel = $this->riskLevel($riskScore, $heavyRedFlags, $mediumRedFlags);
        $suspiciousFlags = $this->suspiciousFlags($participant, $answers, $answerByNumber, $categoryScores, $scorableIdealCount, $scorableCount);
        $contradictionCount = count(array_filter($suspiciousFlags, fn (array $flag): bool => $flag['type'] === 'contradiction'));
        $honestyStatus = $this->honestyStatus($contradictionCount, $suspiciousFlags);
        $memberType = $this->memberType($communityFit, $competitiveFit, $riskLevel, $categoryScores, $honestyStatus, $heavyRedFlags);
        $finalStatus = $this->finalStatus($communityFit, $competitiveFit, $riskLevel, $honestyStatus, $heavyRedFlags, $suspiciousFlags);
        $profile = $this->profileResult($profileBuckets);
        $riskReasons = $this->riskReasons($riskScore, $riskLevel, $categoryScores, $redFlags, $suspiciousFlags);

        return [
            'answer_scores' => $answerScores,
            'category_scores' => $categoryScores,
            'community_fit_score' => $communityFit,
            'competitive_fit_score' => $competitiveFit,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'honesty_status' => $honestyStatus,
            'member_type' => $memberType,
            'final_status' => $finalStatus,
            'profile_code' => $profile['code'],
            'profile_name' => $profile['name'],
            'profile_breakdown' => $profile['breakdown'],
            'red_flags' => $redFlags,
            'suspicious_flags' => $suspiciousFlags,
            'risk_reasons' => $riskReasons,
        ];
    }

    private function scoreAnswer(Question $question, string $answerValue): int
    {
        if ($question->question_type === 'situational') {
            return (int) ($question->scoring_map[$answerValue] ?? 0);
        }

        $value = (int) $answerValue;

        return str_starts_with($question->scoring_direction, 'reverse')
            ? 5 - $value
            : $value;
    }

    /**
     * @return array<int, string>
     */
    private function categories(?string $category): array
    {
        return collect(explode(',', (string) $category))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array{raw: int, min: int, max: int, items: int}>  $buckets
     * @return array<string, array<string, int>>
     */
    private function categoryScores(array $buckets): array
    {
        $scores = [];

        foreach ($buckets as $category => $bucket) {
            $range = max(1, $bucket['max'] - $bucket['min']);
            $scores[$category] = [
                'score' => (int) round((($bucket['raw'] - $bucket['min']) / $range) * 100),
                'raw' => $bucket['raw'],
                'min' => $bucket['min'],
                'max' => $bucket['max'],
                'items' => $bucket['items'],
            ];
        }

        ksort($scores);

        return $scores;
    }

    /**
     * @param  array<string, array<string, int>>  $categoryScores
     * @param  array<string, int>  $weights
     */
    private function weightedScore(array $categoryScores, array $weights): int
    {
        $score = 0;

        foreach ($weights as $category => $weight) {
            $score += ($categoryScores[$category]['score'] ?? 0) * ($weight / 100);
        }

        return (int) round($score);
    }

    /**
     * @param  array<string, array<string, int>>  $categoryScores
     */
    private function riskScore(array $categoryScores): int
    {
        $score = ((100 - ($categoryScores['Toxicity Control']['score'] ?? 0)) * 0.25)
            + ((100 - ($categoryScores['Conflict Handling']['score'] ?? 0)) * 0.20)
            + ((100 - ($categoryScores['Rule Acceptance']['score'] ?? 0)) * 0.20)
            + ((100 - ($categoryScores['Accountability']['score'] ?? 0)) * 0.15)
            + ((100 - ($categoryScores['Drama Risk']['score'] ?? 0)) * 0.20);

        return (int) round($score);
    }

    private function riskLevel(int $riskScore, int $heavyRedFlags, int $mediumRedFlags): string
    {
        if ($riskScore >= 80 || $heavyRedFlags >= 2) {
            return 'Critical';
        }

        if ($riskScore >= 65 || $heavyRedFlags === 1) {
            return 'High';
        }

        if ($riskScore >= 35 || $mediumRedFlags >= 2) {
            return 'Medium';
        }

        if ($riskScore >= 20) {
            return 'Low';
        }

        return 'Very Low';
    }

    private function isLowOrBelowRisk(string $riskLevel): bool
    {
        return in_array($riskLevel, ['Very Low', 'Low'], true);
    }

    private function isHighOrAboveRisk(string $riskLevel): bool
    {
        return in_array($riskLevel, ['High', 'Critical'], true);
    }

    private function isMediumOrAboveRisk(string $riskLevel): bool
    {
        return in_array($riskLevel, ['Medium', 'High', 'Critical'], true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function situationalFlags(Question $question, string $answerValue, int $score): array
    {
        if (in_array($answerValue, $question->red_flag_options ?? [], true)) {
            return [[
                'type' => 'situational_red_flag',
                'severity' => 'heavy',
                'question_number' => $question->question_number,
                'answer_value' => $answerValue,
                'message' => 'Jawaban situasional masuk red flag berat.',
                'risk_tags' => $question->risk_tags ?? [],
            ]];
        }

        if ($score === 1) {
            return [[
                'type' => 'situational_risky_answer',
                'severity' => 'medium',
                'question_number' => $question->question_number,
                'answer_value' => $answerValue,
                'message' => 'Jawaban situasional menunjukkan risiko sedang.',
                'risk_tags' => $question->risk_tags ?? [],
            ]];
        }

        return [];
    }

    /**
     * @param  Collection<int, Answer>  $answers
     * @param  Collection<int, Answer>  $answerByNumber
     * @param  array<string, array<string, int>>  $categoryScores
     * @return array<int, array<string, mixed>>
     */
    private function suspiciousFlags(
        Participant $participant,
        Collection $answers,
        Collection $answerByNumber,
        array $categoryScores,
        int $idealCount,
        int $scorableCount,
    ): array {
        $flags = [];
        $thresholds = $this->settings->thresholds();
        $sessions = $participant->sessions()->get();
        $startedAt = $participant->accessCode?->started_at;
        $completedAt = $participant->accessCode?->completed_at ?? now();

        if ($startedAt) {
            $durationMinutes = $startedAt->diffInMinutes($completedAt);

            if ($durationMinutes < $thresholds['high_speed_minutes']) {
                $flags[] = $this->flag('speed', 'high', "Durasi assessment sangat cepat ({$durationMinutes} menit).");
            } elseif ($durationMinutes < $thresholds['min_duration_minutes']) {
                $flags[] = $this->flag('speed', 'medium', "Durasi assessment cepat ({$durationMinutes} menit).");
            }
        }

        $likertAnswers = $answers->filter(fn (Answer $answer): bool => $answer->question->question_type === 'likert'
            && ! $answer->question->profile_axis);
        $mostCommon = $likertAnswers->count() > 0
            ? $likertAnswers->countBy('answer_value')->max() / $likertAnswers->count()
            : 0;

        if ($mostCommon >= $thresholds['straight_lining_high']) {
            $flags[] = $this->flag('straight_lining', 'high', 'Pola jawaban Likert sangat seragam.');
        } elseif ($mostCommon >= $thresholds['straight_lining_medium']) {
            $flags[] = $this->flag('straight_lining', 'medium', 'Pola jawaban Likert cukup seragam.');
        }

        $perfectionIndex = $scorableCount > 0 ? $idealCount / $scorableCount : 0;
        $extremeHonestyCount = collect([54, 55, 58, 83, 85])
            ->filter(fn (int $number): bool => (string) $answerByNumber->get($number)?->answer_value === '4')
            ->count();

        if ($perfectionIndex >= $thresholds['perfection_high'] && $extremeHonestyCount >= 2) {
            $flags[] = $this->flag('impossible_perfection', 'high', 'Pola jawaban terlalu sempurna dan didukung klaim ekstrem.');
        } elseif ($perfectionIndex >= $thresholds['perfection_medium']) {
            $flags[] = $this->flag('answer_polishing', 'medium', 'Pola jawaban sangat ideal dan perlu review ringan.');
        }

        $extremeLikertRate = $likertAnswers->count() > 0
            ? $likertAnswers->filter(fn (Answer $answer): bool => in_array((string) $answer->answer_value, ['1', '4'], true))->count() / $likertAnswers->count()
            : 0;

        if ($extremeLikertRate >= 0.92) {
            $flags[] = $this->flag('extreme_response_pattern', 'medium', 'Pola jawaban ekstrem muncul sangat sering.');
        }

        $refreshCount = (int) $sessions->sum('refresh_count');
        if ($refreshCount > $thresholds['refresh_count']) {
            $flags[] = $this->flag('refresh_count', 'medium', "Refresh count tinggi ({$refreshCount}).");
        }

        $resumeCount = (int) $sessions->sum('resume_count');
        if ($resumeCount > ($thresholds['resume_count'] ?? 8)) {
            $flags[] = $this->flag('resume_pattern', 'medium', "Resume count tinggi ({$resumeCount}).");
        }

        $deviceCount = $sessions->pluck('device_id')->unique()->count();
        if ($deviceCount > $thresholds['device_count']) {
            $flags[] = $this->flag('device_count', 'medium', "Device count melebihi batas ({$deviceCount}).");
        }

        $veryFastAnswers = $answers
            ->filter(fn (Answer $answer): bool => $answer->client_duration_seconds !== null && $answer->client_duration_seconds < ($thresholds['min_answer_seconds'] ?? 2))
            ->count();

        if ($veryFastAnswers >= ($thresholds['fast_answer_count'] ?? 12)) {
            $flags[] = $this->flag('answer_timing', 'medium', "Banyak jawaban sangat cepat ({$veryFastAnswers} soal).");
        }

        $visibilityChanges = (int) $answers->sum('visibility_change_count');
        if ($visibilityChanges > ($thresholds['visibility_change_count'] ?? 20)) {
            $flags[] = $this->flag('tab_visibility', 'medium', "Tab visibility berubah cukup sering ({$visibilityChanges}).");
        }

        $offlineSyncs = (int) $answers->sum('offline_sync_count');
        if ($offlineSyncs > ($thresholds['offline_sync_count'] ?? 5)) {
            $flags[] = $this->flag('offline_sync', 'medium', "Offline sync terjadi berulang ({$offlineSyncs}).");
        }

        return [
            ...$flags,
            ...$this->consistencyPairFlags($answers, $answerByNumber),
            ...$this->contradictionFlags($answerByNumber, $categoryScores),
        ];
    }

    /**
     * @param  Collection<int, Answer>  $answers
     * @param  Collection<int, Answer>  $answerByNumber
     * @return array<int, array<string, mixed>>
     */
    private function consistencyPairFlags(Collection $answers, Collection $answerByNumber): array
    {
        $flags = [];

        $answers
            ->filter(fn (Answer $answer): bool => $answer->question->is_consistency_item && $answer->question->consistency_pair_id)
            ->groupBy(fn (Answer $answer): string => (string) $answer->question->consistency_pair_id)
            ->each(function (Collection $group, string $pairId) use (&$flags, $answerByNumber): void {
                $numbers = $group
                    ->pluck('question.question_number')
                    ->map(fn (int $number): int => $number)
                    ->sort()
                    ->values()
                    ->all();

                if (count($numbers) < 2) {
                    return;
                }

                $first = (int) $answerByNumber->get($numbers[0])?->answer_value;
                $second = (int) $answerByNumber->get($numbers[1])?->answer_value;

                if (abs($first - $second) >= 3) {
                    $flags[] = $this->flag(
                        'consistency_pair_mismatch',
                        'medium',
                        "Pair {$pairId} menunjukkan jawaban yang sangat berjauhan.",
                        $numbers,
                    );
                }
            });

        return $flags;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function emptyProfileBuckets(): array
    {
        return collect($this->profileAxisDefinitions())
            ->map(function (array $axis): array {
                $poles = collect($axis['poles'])
                    ->map(fn (array $pole): array => [...$pole, 'score' => 0])
                    ->all();

                return [
                    ...$axis,
                    'poles' => $poles,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function profileAxisDefinitions(): array
    {
        return [
            'social' => [
                'label' => 'Social Energy',
                'poles' => [
                    'S' => ['label' => 'Social Connector'],
                    'Q' => ['label' => 'Quiet Steady'],
                ],
                'neutral' => 'Q',
            ],
            'play_drive' => [
                'label' => 'Play Drive',
                'poles' => [
                    'R' => ['label' => 'Racer Drive'],
                    'C' => ['label' => 'Casual Community'],
                ],
                'neutral' => 'C',
            ],
            'rule_style' => [
                'label' => 'Rule Style',
                'poles' => [
                    'A' => ['label' => 'Admin-Aligned'],
                    'N' => ['label' => 'Needs Rationale'],
                ],
                'neutral' => 'A',
            ],
            'conflict_style' => [
                'label' => 'Conflict Style',
                'poles' => [
                    'C' => ['label' => 'Calm Resolver'],
                    'E' => ['label' => 'Expressive Responder'],
                ],
                'neutral' => 'C',
            ],
            'team_style' => [
                'label' => 'Team Style',
                'poles' => [
                    'T' => ['label' => 'Team-Oriented'],
                    'I' => ['label' => 'Independent Runner'],
                ],
                'neutral' => 'T',
            ],
            'competitive_style' => [
                'label' => 'Competitive Style',
                'poles' => [
                    'D' => ['label' => 'Driven Competitor'],
                    'G' => ['label' => 'Graceful Competitor'],
                    'B' => ['label' => 'Balanced Competitor', 'virtual' => true],
                ],
                'neutral' => 'B',
            ],
            'drama_resistance' => [
                'label' => 'Drama Resistance',
                'poles' => [
                    'L' => ['label' => 'Low-Drama Anchor'],
                    'R' => ['label' => 'Reactive Concern'],
                    'S' => ['label' => 'Stable Observer', 'virtual' => true],
                ],
                'neutral' => 'S',
            ],
            'feedback_style' => [
                'label' => 'Admin Feedback Style',
                'poles' => [
                    'F' => ['label' => 'Feedback-Ready'],
                    'J' => ['label' => 'Justification-Seeking'],
                    'P' => ['label' => 'Processing First', 'virtual' => true],
                ],
                'neutral' => 'P',
            ],
            'interaction_style' => [
                'label' => 'Voice/Chat Interaction Style',
                'poles' => [
                    'V' => ['label' => 'Voice-Comfortable'],
                    'T' => ['label' => 'Text-First'],
                    'B' => ['label' => 'Balanced Interaction', 'virtual' => true],
                ],
                'neutral' => 'B',
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $buckets
     */
    private function addProfileAnswer(array &$buckets, string $axis, string $pole, int $answerValue): void
    {
        if (! isset($buckets[$axis]['poles'][$pole])) {
            return;
        }

        $buckets[$axis]['poles'][$pole]['score'] += $answerValue;

        foreach ($buckets[$axis]['poles'] as $otherPole => $meta) {
            if ($otherPole !== $pole && empty($meta['virtual'])) {
                $buckets[$axis]['poles'][$otherPole]['score'] += 5 - $answerValue;
            }
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $buckets
     * @return array{code: string, name: string, breakdown: array<string, mixed>}
     */
    private function profileResult(array $buckets): array
    {
        $codeParts = [];
        $breakdown = [];

        foreach ($buckets as $axis => $bucket) {
            $scores = collect($bucket['poles'])
                ->filter(fn (array $pole): bool => empty($pole['virtual']))
                ->map(fn (array $pole): int => (int) $pole['score']);
            $winner = (string) $scores->sortDesc()->keys()->first();
            $topScores = $scores->sortDesc()->values();

            if ($topScores->count() > 1 && abs($topScores[0] - $topScores[1]) <= 1) {
                $winner = (string) $bucket['neutral'];
            }

            $runnerUp = (int) ($topScores[1] ?? 0);
            $winnerScore = (int) ($scores[$winner] ?? 0);
            $margin = abs($winnerScore - $runnerUp);

            $codeParts[] = $winner;
            $breakdown[$axis] = [
                'label' => $bucket['label'],
                'selected_pole' => $winner,
                'selected_label' => $bucket['poles'][$winner]['label'],
                'scores' => collect($bucket['poles'])->map(fn (array $pole): int => (int) $pole['score'])->all(),
                'margin' => $margin,
                'confidence' => $this->profileConfidence($margin),
                ...$this->profileAxisDetails($axis, $winner),
            ];
        }

        $code = implode('-', $codeParts);
        $profileDetails = $this->profileDetails($code);
        $breakdown['_profile'] = $profileDetails;

        return [
            'code' => $code,
            'name' => $profileDetails['name'],
            'breakdown' => $breakdown,
        ];
    }

    /**
     * @return array{name: string, description: string, strengths: array<int, string>, watchouts: array<int, string>, admin_guidance: string, best_fit: string}
     */
    private function profileDetails(string $code): array
    {
        return [
            'SRAC' => [
                'name' => 'Composed Race Captain',
                'description' => 'Aktif secara sosial, kompetitif, mudah mengikuti arahan admin, dan cenderung tenang saat konflik. Biasanya cocok untuk role race/event yang butuh energi sekaligus kontrol diri.',
                'strengths' => ['Bisa menghidupkan suasana race tanpa membuatnya terlalu panas.', 'Mudah diarahkan oleh admin dan relatif stabil saat ada gesekan.'],
                'watchouts' => ['Tetap perlu batas jelas agar ambisi kompetitif tidak mengambil alih suasana komunitas.'],
                'admin_guidance' => 'Cocok diarahkan ke race/time trial, helper event, atau contoh onboarding kompetitif sehat.',
                'best_fit' => 'Competitive event member, race helper, calon role model kompetitif.',
            ],
            'SRAE' => [
                'name' => 'Hype Racer',
                'description' => 'Sosial, sangat terdorong kompetisi, patuh arahan, tetapi lebih ekspresif saat ada masalah. Energinya bisa bagus untuk event, selama diberi batas komunikasi yang jelas.',
                'strengths' => ['Mudah membuat event terasa hidup.', 'Biasanya cepat terlibat dan punya dorongan improve tinggi.'],
                'watchouts' => ['Ekspresi saat kecewa bisa terbaca terlalu kuat jika tidak diarahkan.'],
                'admin_guidance' => 'Berikan rules of engagement untuk chat race, trash talk, dan cara protes yang aman.',
                'best_fit' => 'Event hype member, competitive participant dengan onboarding komunikasi.',
            ],
            'SRNC' => [
                'name' => 'Strategic Competitor',
                'description' => 'Sosial dan kompetitif, tetapi lebih nyaman jika aturan punya alasan yang jelas. Cenderung tenang dalam konflik dan bisa menjadi peserta race yang kritis tapi terkendali.',
                'strengths' => ['Bisa memberi masukan race yang cukup konstruktif.', 'Kompetitif tanpa harus reaktif.'],
                'watchouts' => ['Butuh ruang bertanya agar tidak merasa aturan dibuat sepihak.'],
                'admin_guidance' => 'Jelaskan alasan rules, lalu arahkan feedback lewat channel resmi.',
                'best_fit' => 'Competitive member, feedback tester untuk event/rules.',
            ],
            'SRNE' => [
                'name' => 'Expressive Challenger',
                'description' => 'Sosial, kompetitif, butuh alasan aturan, dan ekspresif saat tidak setuju. Bisa menjadi aset race, tapi perlu guardrail karena gaya komunikasinya mudah memicu debat.',
                'strengths' => ['Energi tinggi dan berani menyampaikan masukan.', 'Bisa cepat mengangkat isu aturan yang kurang jelas.'],
                'watchouts' => ['Rentan memperpanjang debat publik jika merasa tidak didengar.'],
                'admin_guidance' => 'Wajibkan jalur feedback private/mod ticket dan pantau masa trial di event kompetitif.',
                'best_fit' => 'Competitive trial, manual review, atau watchlist ringan tergantung risk score.',
            ],
            'SCAC' => [
                'name' => 'Community Host',
                'description' => 'Sosial, casual-oriented, mudah mengikuti arahan, dan tenang saat konflik. Biasanya cocok untuk membangun suasana hangout dan menyambut member baru.',
                'strengths' => ['Membantu komunitas terasa ramah.', 'Tidak menjadikan skill race sebagai pusat identitas.'],
                'watchouts' => ['Mungkin kurang terdorong untuk event kompetitif serius.'],
                'admin_guidance' => 'Arahkan ke onboarding sosial, welcoming, atau casual event.',
                'best_fit' => 'Casual community member, greeter, support sosial.',
            ],
            'SCAE' => [
                'name' => 'Energetic Hangout',
                'description' => 'Sosial, lebih casual, mengikuti arahan admin, dan cukup ekspresif. Cocok untuk membuat channel hidup, tapi tetap perlu arahan agar ekspresi tidak menjadi spam/debat.',
                'strengths' => ['Mudah berbaur dan menghidupkan obrolan.', 'Biasanya nyaman di aktivitas santai.'],
                'watchouts' => ['Perlu batas frekuensi chat/mention jika terlalu bersemangat.'],
                'admin_guidance' => 'Berikan aturan chat ringan dan dorong kontribusi di channel casual.',
                'best_fit' => 'Hangout member, casual event participant.',
            ],
            'SCNC' => [
                'name' => 'Thoughtful Organizer',
                'description' => 'Sosial dan casual, tetapi suka memahami alasan aturan. Cenderung tenang, sehingga cocok membantu merapikan suasana dan memberi masukan tanpa banyak drama.',
                'strengths' => ['Mampu menjembatani admin dan member casual.', 'Masukan biasanya lebih mudah diarahkan.'],
                'watchouts' => ['Bisa lambat commit jika aturan terasa belum jelas.'],
                'admin_guidance' => 'Libatkan dalam feedback ringan atau kegiatan komunitas yang butuh komunikasi rapi.',
                'best_fit' => 'Supportive member, casual organizer, feedback helper.',
            ],
            'SCNE' => [
                'name' => 'Social Challenger',
                'description' => 'Sosial, casual-oriented, butuh alasan aturan, dan ekspresif. Bisa memperkaya diskusi, tetapi perlu batas agar masukan tidak berubah menjadi debat publik.',
                'strengths' => ['Berani bicara saat ada hal yang terasa kurang cocok.', 'Mudah membaca suasana sosial.'],
                'watchouts' => ['Perlu diarahkan ke cara menyampaikan concern yang tidak memancing kubu.'],
                'admin_guidance' => 'Berikan channel feedback resmi dan pantau respons saat aturan dijelaskan.',
                'best_fit' => 'Casual member dengan trial komunikasi.',
            ],
            'QRAC' => [
                'name' => 'Quiet Grinder',
                'description' => 'Lebih pendiam, kompetitif, mengikuti arahan admin, dan tenang saat konflik. Biasanya kuat sebagai peserta race yang fokus tanpa banyak drama.',
                'strengths' => ['Fokus improve tanpa banyak mencari perhatian.', 'Relatif mudah diarahkan dan stabil.'],
                'watchouts' => ['Mungkin perlu diajak agar tidak terlalu terisolasi dari komunitas.'],
                'admin_guidance' => 'Arahkan ke race/time trial dan beri onboarding sosial ringan.',
                'best_fit' => 'Quiet competitive member, time trial participant.',
            ],
            'QRAE' => [
                'name' => 'Focused Spark',
                'description' => 'Lebih pendiam tetapi kompetitif, patuh arahan, dan bisa ekspresif saat ada masalah. Biasanya fokus bermain, namun perlu kanal aman untuk menyampaikan frustrasi.',
                'strengths' => ['Dorongan improve tinggi tanpa harus selalu ramai di chat.', 'Bisa cepat menunjukkan concern penting.'],
                'watchouts' => ['Saat frustrasi, ekspresi singkat bisa terasa tajam.'],
                'admin_guidance' => 'Berikan jalur laporan private dan cek respons saat kalah atau ditegur.',
                'best_fit' => 'Competitive member dengan monitoring komunikasi ringan.',
            ],
            'QRNC' => [
                'name' => 'Analytical Runner',
                'description' => 'Pendiam, kompetitif, butuh alasan aturan, dan tenang. Biasanya cocok untuk improve serius dan feedback teknis karena tidak terlalu reaktif.',
                'strengths' => ['Bisa memberi masukan yang detail dan tidak ramai.', 'Kompetitif dengan kontrol konflik yang baik.'],
                'watchouts' => ['Perlu penjelasan rules agar tetap trust ke sistem admin.'],
                'admin_guidance' => 'Libatkan dalam test map/rules jika risk rendah dan feedback-nya konstruktif.',
                'best_fit' => 'Map/race tester, analytical competitive member.',
            ],
            'QRNE' => [
                'name' => 'Independent Racer',
                'description' => 'Pendiam, kompetitif, butuh rationale, dan ekspresif saat tidak setuju. Bisa sangat mandiri, tetapi perlu trust-building dengan admin.',
                'strengths' => ['Fokus performa dan tidak terlalu bergantung pada validasi chat.', 'Berani menyampaikan masalah aturan.'],
                'watchouts' => ['Jika merasa aturan tidak adil, bisa sulit diarahkan tanpa penjelasan kuat.'],
                'admin_guidance' => 'Cocok trial dengan ekspektasi feedback formal dan batas debat publik.',
                'best_fit' => 'Independent competitive trial.',
            ],
            'QCAC' => [
                'name' => 'Steady Supporter',
                'description' => 'Pendiam, casual, mengikuti arahan admin, dan tenang saat konflik. Biasanya aman untuk komunitas sebagai member stabil yang tidak banyak menimbulkan drama.',
                'strengths' => ['Stabil, rendah drama, dan tidak menekan member lain untuk kompetitif.', 'Cocok menjaga suasana tetap santai.'],
                'watchouts' => ['Engagement bisa terlihat rendah jika tidak diberi ruang yang nyaman.'],
                'admin_guidance' => 'Ajak lewat aktivitas ringan dan jangan nilai rendah hanya karena tidak ramai.',
                'best_fit' => 'Casual community member, quiet but safe.',
            ],
            'QCAE' => [
                'name' => 'Warm Casual',
                'description' => 'Pendiam-casual tetapi lebih ekspresif saat ada hal yang dirasakan. Bisa hangat dalam komunitas kecil, dengan sedikit arahan komunikasi.',
                'strengths' => ['Tidak terlalu mengejar kompetisi dan bisa nyaman di ruang santai.', 'Cukup terbuka menyampaikan kebutuhan.'],
                'watchouts' => ['Perlu dibantu membedakan ekspresi personal dan konflik publik.'],
                'admin_guidance' => 'Cocok casual onboarding dengan arahan kapan memakai private channel.',
                'best_fit' => 'Casual member, social-lite participant.',
            ],
            'QCNC' => [
                'name' => 'Calm Observer',
                'description' => 'Pendiam, casual, butuh alasan aturan, dan tenang. Biasanya aman, observatif, dan cocok masuk komunitas tanpa tekanan kompetitif.',
                'strengths' => ['Tidak mudah memanaskan konflik.', 'Bisa memahami aturan jika diberi konteks.'],
                'watchouts' => ['Mungkin butuh waktu lebih lama untuk merasa terlibat.'],
                'admin_guidance' => 'Berikan onboarding jelas, role casual, dan ajakan bertahap.',
                'best_fit' => 'Quiet casual member, low-drama observer.',
            ],
            'QCNE' => [
                'name' => 'Independent Casual',
                'description' => 'Pendiam, casual, butuh alasan aturan, dan ekspresif saat ada masalah. Biasanya tidak mengejar kompetisi, tetapi tetap perlu kanal feedback yang jelas.',
                'strengths' => ['Mandiri dan tidak terlalu bergantung pada leaderboard.', 'Bisa menyampaikan concern yang dirasakan.'],
                'watchouts' => ['Jika merasa tidak cocok, bisa cepat menunjukkan ketidaksetujuan.'],
                'admin_guidance' => 'Onboarding perlu menjelaskan aturan, ekspektasi komunikasi, dan kanal feedback private.',
                'best_fit' => 'Casual trial atau manual review ringan tergantung risk/honesty.',
            ],
        ][$code] ?? [
            'name' => 'Community Style '.$code,
            'description' => 'Kode ini merangkum gaya interaksi komunitas peserta berdasarkan axis SL Profile terbaru. Ini indikator research-informed untuk konteks admin, bukan diagnosis, MBTI, label klinis, atau dasar otomatis final decision.',
            'strengths' => ['Baca bersama profile breakdown per axis untuk melihat potensi kontribusi komunitas.', 'Gunakan bersama category score, risk reasons, dan konteks interview admin.'],
            'watchouts' => ['Jangan gunakan profile code sebagai vonis tunggal.', 'Confidence rendah pada axis tertentu berarti gaya peserta cenderung seimbang atau butuh konteks tambahan.'],
            'admin_guidance' => 'Gunakan sebagai bahan onboarding, interview, atau trial expectation; keputusan final tetap perlu review admin.',
            'best_fit' => 'Manual admin context.',
        ];
    }

    /**
     * @return array{summary: string, description: string, strengths: array<int, string>, watchouts: array<int, string>, admin_guidance: string}
     */
    private function profileAxisDetails(string $axis, string $pole): array
    {
        return [
            'social:S' => [
                'summary' => 'Aktif membuka interaksi dan membantu member baru merasa diterima.',
                'description' => 'Social Connector biasanya lebih cepat masuk ke obrolan, menyapa orang baru, dan membuat channel terasa hidup.',
                'strengths' => ['Membantu onboarding sosial.', 'Mudah diajak ikut aktivitas komunitas.'],
                'watchouts' => ['Pastikan energi sosial tidak berubah menjadi spam, mention berlebih, atau dominasi chat.'],
                'admin_guidance' => 'Cocok diberi ruang menyambut member baru atau ikut event sosial jika risk rendah.',
            ],
            'social:Q' => [
                'summary' => 'Lebih tenang, observatif, dan stabil walau tidak dominan di chat.',
                'description' => 'Quiet Steady biasanya perlu waktu untuk nyaman, tetapi tetap bisa menjadi member aman dan konsisten.',
                'strengths' => ['Rendah kebutuhan perhatian.', 'Cenderung tidak memanaskan chat.'],
                'watchouts' => ['Jangan dianggap tidak cocok hanya karena tidak terlalu ramai.'],
                'admin_guidance' => 'Ajak secara bertahap lewat aktivitas kecil atau channel yang tidak terlalu ramai.',
            ],
            'play_drive:R' => [
                'summary' => 'Termotivasi oleh improve, race, leaderboard, dan latihan berulang.',
                'description' => 'Racer Drive melihat kompetisi sebagai sumber energi utama dan biasanya suka mengukur progres.',
                'strengths' => ['Potensial aktif di race/time trial.', 'Bisa mengangkat kualitas event kompetitif.'],
                'watchouts' => ['Perlu batas agar ambisi tidak berubah menjadi meremehkan member casual.'],
                'admin_guidance' => 'Berikan aturan kompetisi, etika menang/kalah, dan kanal feedback race sejak onboarding.',
            ],
            'play_drive:C' => [
                'summary' => 'Lebih menikmati komunitas sebagai ruang santai daripada pembuktian skill.',
                'description' => 'Casual Community biasanya mencari hangout, teman main, dan suasana aman lebih dari posisi leaderboard.',
                'strengths' => ['Mendukung budaya komunitas yang ramah untuk non-racer.', 'Tidak membuat skill sebagai syarat sosial.'],
                'watchouts' => ['Mungkin kurang aktif di event kompetitif serius.'],
                'admin_guidance' => 'Arahkan ke event casual dan jangan jadikan competitive fit rendah sebagai alasan penolakan tunggal.',
            ],
            'rule_style:A' => [
                'summary' => 'Mudah mengikuti arahan admin dan menyesuaikan diri dengan aturan.',
                'description' => 'Admin-Aligned cenderung menerima struktur komunitas dan lebih mudah diarahkan saat aturan berubah.',
                'strengths' => ['Memudahkan onboarding.', 'Lebih stabil saat ada keputusan admin.'],
                'watchouts' => ['Tetap beri ruang bertanya agar kepatuhan tidak sekadar pasif.'],
                'admin_guidance' => 'Cocok diberi onboarding standar dan ekspektasi aturan yang jelas.',
            ],
            'rule_style:N' => [
                'summary' => 'Butuh alasan yang jelas dan ruang masukan sebelum nyaman dengan aturan.',
                'description' => 'Needs Rationale bukan berarti melawan aturan; sering kali mereka lebih kooperatif setelah memahami alasan aturan.',
                'strengths' => ['Bisa memberi feedback untuk rules yang kurang jelas.', 'Membantu admin melihat celah komunikasi.'],
                'watchouts' => ['Jika tidak diberi konteks, bisa terlihat menantang atau sulit diarahkan.'],
                'admin_guidance' => 'Jelaskan alasan rules, arahkan masukan lewat private/mod ticket, dan batasi debat publik.',
            ],
            'conflict_style:C' => [
                'summary' => 'Menahan diri, memakai jalur private, dan menghindari konflik publik.',
                'description' => 'Calm Resolver cenderung memberi jeda sebelum merespons dan lebih aman dalam situasi panas.',
                'strengths' => ['Mengurangi risiko drama publik.', 'Lebih mudah diajak mediasi.'],
                'watchouts' => ['Bisa memendam masalah jika tidak diberi kanal aman.'],
                'admin_guidance' => 'Sediakan jalur private untuk concern dan cek berkala saat masa trial.',
            ],
            'conflict_style:E' => [
                'summary' => 'Cepat mengekspresikan ketidaksetujuan saat merasa ada masalah.',
                'description' => 'Expressive Responder biasanya jujur dan cepat menyuarakan concern, tetapi perlu batas agar tidak memicu konflik publik.',
                'strengths' => ['Masalah bisa cepat terlihat.', 'Berani menyampaikan ketidaknyamanan.'],
                'watchouts' => ['Rentan memperpanjang debat jika emosi belum turun.'],
                'admin_guidance' => 'Tekankan cooldown, private channel, dan aturan anti-flame sejak awal.',
            ],
            'team_style:T' => [
                'summary' => 'Cenderung nyaman bekerja sama dan menjaga koordinasi tim.',
                'description' => 'Team-Oriented biasanya lebih mudah diajak event yang butuh komunikasi, saling bantu, dan pembagian peran.',
                'strengths' => ['Membantu ritme event tim.', 'Lebih natural memberi support ke teammate.'],
                'watchouts' => ['Pastikan tidak terlalu mengambil alih keputusan tim.'],
                'admin_guidance' => 'Cocok untuk event tim atau onboarding berbasis buddy jika risk rendah.',
            ],
            'team_style:I' => [
                'summary' => 'Lebih nyaman membangun progress sendiri sebelum banyak koordinasi.',
                'description' => 'Independent Runner biasanya fokus pada performa personal dan bisa tetap cocok selama ekspektasi kerja sama dibuat jelas.',
                'strengths' => ['Mandiri dan tidak terlalu bergantung pada grup.', 'Cocok untuk time trial atau latihan individual.'],
                'watchouts' => ['Perlu arahan saat event mengharuskan komunikasi tim.'],
                'admin_guidance' => 'Berikan role individual lebih dulu, lalu ajak kerja sama secara bertahap.',
            ],
            'competitive_style:D' => [
                'summary' => 'Dorongan kompetitif kuat dan fokus mengejar hasil terbaik.',
                'description' => 'Driven Competitor biasanya termotivasi oleh leaderboard, improve, dan standar performa tinggi.',
                'strengths' => ['Bisa menaikkan kualitas race.', 'Konsisten mengejar progress.'],
                'watchouts' => ['Perlu batas agar tidak menekan member casual atau teammate.'],
                'admin_guidance' => 'Tekankan sportivitas, etika menang/kalah, dan cara feedback race sejak onboarding.',
            ],
            'competitive_style:G' => [
                'summary' => 'Kompetitif tetapi menjaga lawan dan teammate tetap nyaman.',
                'description' => 'Graceful Competitor bisa serius mengejar hasil tanpa menjadikan skill sebagai alasan merendahkan orang lain.',
                'strengths' => ['Cocok untuk race sehat.', 'Bisa jadi contoh kompetisi yang ramah.'],
                'watchouts' => ['Tetap cek respons saat kalah tipis atau event terasa tidak adil.'],
                'admin_guidance' => 'Cocok diarahkan ke event kompetitif dengan ekspektasi sportsmanship.',
            ],
            'competitive_style:B' => [
                'summary' => 'Dorongan kompetitif dan kenyamanan sosial terlihat seimbang.',
                'description' => 'Balanced Competitor biasanya bisa menikmati kompetisi tanpa selalu harus menjadikannya pusat identitas.',
                'strengths' => ['Fleksibel antara event race dan aktivitas santai.', 'Tidak terlalu mudah terjebak tekanan leaderboard.'],
                'watchouts' => ['Perlu dilihat konteks category score untuk mengetahui motivasi utama.'],
                'admin_guidance' => 'Cocok untuk trial umum sambil melihat event mana yang paling natural.',
            ],
            'drama_resistance:L' => [
                'summary' => 'Cenderung menahan diri dan tidak memperpanjang drama kecil.',
                'description' => 'Low-Drama Anchor biasanya membantu suasana tetap stabil saat ada rumor, salah paham, atau komentar panas.',
                'strengths' => ['Mengurangi eskalasi.', 'Cocok untuk komunitas yang butuh stabilitas chat.'],
                'watchouts' => ['Bisa terlalu diam saat perlu melapor masalah.'],
                'admin_guidance' => 'Pastikan tahu jalur report private jika melihat masalah serius.',
            ],
            'drama_resistance:R' => [
                'summary' => 'Cepat ingin membahas masalah agar terasa jelas.',
                'description' => 'Reactive Concern bisa membantu isu cepat terlihat, tetapi perlu kanal agar pembahasan tidak berubah menjadi drama publik.',
                'strengths' => ['Masalah tidak selalu dipendam.', 'Bisa memberi sinyal awal jika ada gesekan.'],
                'watchouts' => ['Rentan memperbesar isu jika dibahas di public chat.'],
                'admin_guidance' => 'Arahkan ke mod ticket/private channel dan batasi pembahasan rumor.',
            ],
            'drama_resistance:S' => [
                'summary' => 'Stabil di tengah isu, tetapi tetap perlu konteks tambahan.',
                'description' => 'Stable Observer menunjukkan pola seimbang antara tidak memperkeruh suasana dan tetap ingin memahami masalah.',
                'strengths' => ['Tidak mudah terseret drama.', 'Masih bisa diajak klarifikasi dengan tenang.'],
                'watchouts' => ['Respons bisa berubah tergantung kedekatan dengan pihak yang terlibat.'],
                'admin_guidance' => 'Gunakan interview ringan untuk melihat cara menangani rumor atau konflik teman dekat.',
            ],
            'feedback_style:F' => [
                'summary' => 'Relatif siap menerima feedback admin sebagai bahan perbaikan.',
                'description' => 'Feedback-Ready biasanya lebih mudah diarahkan saat ada teguran atau perubahan aturan.',
                'strengths' => ['Onboarding lebih lancar.', 'Cenderung memperbaiki perilaku setelah ditegur.'],
                'watchouts' => ['Tetap pastikan feedback disampaikan jelas dan tidak mempermalukan.'],
                'admin_guidance' => 'Cocok dengan onboarding standar dan feedback langsung yang sopan.',
            ],
            'feedback_style:J' => [
                'summary' => 'Membutuhkan alasan dan contoh sebelum feedback terasa masuk akal.',
                'description' => 'Justification-Seeking bukan berarti menolak feedback; peserta biasanya lebih kooperatif setelah memahami konteksnya.',
                'strengths' => ['Bisa membantu admin menjelaskan aturan lebih rapi.', 'Sering memberi masukan detail.'],
                'watchouts' => ['Bisa terlihat defensif jika teguran terlalu singkat.'],
                'admin_guidance' => 'Berikan alasan singkat, contoh perilaku, dan batas diskusi publik.',
            ],
            'feedback_style:P' => [
                'summary' => 'Butuh jeda untuk memproses feedback sebelum merespons tenang.',
                'description' => 'Processing First biasanya tidak langsung nyaman saat ditegur, tetapi bisa membaik jika diberi ruang cooldown.',
                'strengths' => ['Dapat menghindari respons impulsif jika diberi waktu.', 'Cocok dengan feedback private.'],
                'watchouts' => ['Respons awal mungkin tampak pasif atau tertahan.'],
                'admin_guidance' => 'Gunakan private feedback dan beri waktu sebelum follow-up.',
            ],
            'interaction_style:V' => [
                'summary' => 'Cukup nyaman berinteraksi lewat voice saat event atau main bareng.',
                'description' => 'Voice-Comfortable biasanya lebih mudah terlibat dalam aktivitas real-time yang butuh koordinasi cepat.',
                'strengths' => ['Membantu koordinasi event.', 'Cepat membangun keakraban saat main bareng.'],
                'watchouts' => ['Pastikan tetap memberi ruang bicara dan menjaga tone.'],
                'admin_guidance' => 'Cocok diarahkan ke event voice dengan aturan komunikasi yang jelas.',
            ],
            'interaction_style:T' => [
                'summary' => 'Lebih nyaman memakai text chat daripada voice.',
                'description' => 'Text-First biasanya butuh ruang komunikasi yang tidak terlalu ramai dan bisa tetap aktif lewat chat.',
                'strengths' => ['Lebih terukur saat menyampaikan pesan.', 'Cocok untuk member yang tidak selalu nyaman voice.'],
                'watchouts' => ['Jangan dianggap kurang engage hanya karena jarang voice.'],
                'admin_guidance' => 'Berikan opsi text coordination dan jangan memaksa voice untuk semua aktivitas.',
            ],
            'interaction_style:B' => [
                'summary' => 'Fleksibel antara voice dan text sesuai situasi.',
                'description' => 'Balanced Interaction biasanya bisa menyesuaikan medium komunikasi selama aturan dan suasananya nyaman.',
                'strengths' => ['Mudah masuk ke berbagai jenis event.', 'Bisa menjembatani member voice dan text.'],
                'watchouts' => ['Preferensi bisa berubah tergantung channel dan orang yang terlibat.'],
                'admin_guidance' => 'Coba beberapa format event untuk melihat medium paling natural.',
            ],
        ]["{$axis}:{$pole}"] ?? [
            'summary' => 'Profil komunitas tercatat untuk konteks admin.',
            'description' => 'Axis profile ini perlu dibaca bersama hasil scoring lain.',
            'strengths' => ['Memberi konteks tambahan untuk admin.'],
            'watchouts' => ['Jangan digunakan sebagai label mutlak.'],
            'admin_guidance' => 'Gunakan sebagai bahan interview atau onboarding.',
        ];
    }

    private function profileConfidence(int $margin): string
    {
        if ($margin >= 6) {
            return 'High';
        }

        if ($margin >= 3) {
            return 'Medium';
        }

        return 'Balanced';
    }

    /**
     * @param  array<string, array<string, int>>  $categoryScores
     * @param  array<int, array<string, mixed>>  $redFlags
     * @param  array<int, array<string, mixed>>  $suspiciousFlags
     * @return array<int, string>
     */
    private function riskReasons(
        int $riskScore,
        string $riskLevel,
        array $categoryScores,
        array $redFlags,
        array $suspiciousFlags,
    ): array {
        $reasons = ["Risk level {$riskLevel} berdasarkan risk score {$riskScore}."];

        foreach (['Toxicity Control', 'Conflict Handling', 'Rule Acceptance', 'Accountability', 'Drama Risk'] as $category) {
            $score = $categoryScores[$category]['score'] ?? null;

            if ($score !== null && $score < 60) {
                $reasons[] = "{$category} rendah ({$score}) dan perlu perhatian admin.";
            }
        }

        $heavyRedFlags = $this->countFlags($redFlags, 'heavy');
        $mediumRedFlags = $this->countFlags($redFlags, 'medium');

        if ($heavyRedFlags > 0) {
            $reasons[] = "{$heavyRedFlags} red flag berat dari jawaban situasional.";
        }

        if ($mediumRedFlags >= 2) {
            $reasons[] = "{$mediumRedFlags} jawaban situasional menunjukkan risiko sedang.";
        }

        $mediumSuspicious = $this->countFlags($suspiciousFlags, 'medium');
        $highSuspicious = $this->countFlags($suspiciousFlags, 'high');

        if ($highSuspicious > 0) {
            $reasons[] = "{$highSuspicious} suspicious flag tinggi memengaruhi validitas review.";
        } elseif ($mediumSuspicious > 0) {
            $reasons[] = "{$mediumSuspicious} suspicious flag sedang perlu dicek admin.";
        }

        if (count($reasons) === 1 && $this->isLowOrBelowRisk($riskLevel)) {
            $reasons[] = 'Tidak ada red flag berat dan kategori risiko utama masih stabil.';
        }

        return $reasons;
    }

    /**
     * @param  Collection<int, Answer>  $answerByNumber
     * @param  array<string, array<string, int>>  $categoryScores
     * @return array<int, array<string, mixed>>
     */
    private function contradictionFlags(Collection $answerByNumber, array $categoryScores): array
    {
        $flags = [];
        $answer = fn (int $number): ?string => $answerByNumber->get($number)?->answer_value;

        if ($answer(54) === '4' && $answer(56) === '1') {
            $flags[] = $this->flag('contradiction', 'medium', 'Q54 dan Q56 menunjukkan klaim reaksi kalah yang bertentangan.', [54, 56]);
        }

        if ($answer(55) === '4' && ($categoryScores['Rule Acceptance']['score'] ?? 100) < 50) {
            $flags[] = $this->flag('contradiction', 'medium', 'Klaim selalu membaca aturan bertentangan dengan Rule Acceptance rendah.', [55]);
        }

        if ($answer(58) === '4' && (($categoryScores['Conflict Handling']['score'] ?? 100) < 50 || ($categoryScores['Drama Risk']['score'] ?? 100) < 50)) {
            $flags[] = $this->flag('contradiction', 'medium', 'Klaim tidak pernah salah paham bertentangan dengan pola konflik/drama.', [58]);
        }

        if ($answer(59) === '1' && $answer(21) === '4') {
            $flags[] = $this->flag('contradiction', 'medium', 'Q59 dan Q21 menunjukkan penerimaan aturan yang bertentangan.', [59, 21]);
        }

        if ($answer(60) === '1' && $answer(40) === '4') {
            $flags[] = $this->flag('contradiction', 'medium', 'Q60 dan Q40 menunjukkan respons aturan yang bertentangan.', [60, 40]);
        }

        if ($answer(83) === '4' && $answer(84) === '1') {
            $flags[] = $this->flag('contradiction', 'medium', 'Q83 dan Q84 menunjukkan klaim membaca suasana chat yang bertentangan.', [83, 84]);
        }

        if ($answer(85) === '4' && $answer(86) === '1') {
            $flags[] = $this->flag('contradiction', 'medium', 'Q85 dan Q86 menunjukkan klaim menerima teguran yang terlalu bertentangan.', [85, 86]);
        }

        if ($answer(85) === '4' && in_array($answer(80), ['C', 'D'], true)) {
            $flags[] = $this->flag('self_report_sjt_contradiction', 'medium', 'Klaim selalu menerima teguran bertentangan dengan respons situasional terhadap teguran admin.', [85, 80]);
        }

        if (($answer(78) === '1' || ($categoryScores['Drama Risk']['score'] ?? 100) >= 80) && in_array($answer(79), ['C', 'D'], true)) {
            $flags[] = $this->flag('self_report_sjt_contradiction', 'medium', 'Self-report low-drama bertentangan dengan respons situasional terhadap rumor/screenshot.', [78, 79]);
        }

        if (($categoryScores['Sportsmanship']['score'] ?? 100) >= 80 && in_array($answer(81), ['C', 'D'], true)) {
            $flags[] = $this->flag('self_report_sjt_contradiction', 'medium', 'Sportsmanship tinggi bertentangan dengan respons situasional saat teammate lambat.', [81]);
        }

        return $flags;
    }

    /**
     * @param  array<int, array<string, mixed>>  $flags
     */
    private function honestyStatus(int $contradictionCount, array $flags): string
    {
        if ($contradictionCount >= 4 || $this->hasSeverity($flags, 'high')) {
            return 'Invalid';
        }

        if ($contradictionCount >= 2 || $this->hasSeverity($flags, 'medium')) {
            return 'Questionable';
        }

        return 'Valid';
    }

    /**
     * @param  array<string, array<string, int>>  $categoryScores
     */
    private function memberType(int $communityFit, int $competitiveFit, string $riskLevel, array $categoryScores, string $honestyStatus, int $heavyRedFlags): string
    {
        $score = fn (string $category): int => $categoryScores[$category]['score'] ?? 0;

        if ($communityFit >= 75 && $competitiveFit >= 75 && $this->isLowOrBelowRisk($riskLevel)) {
            return 'Competitive Racer';
        }

        if ($communityFit >= 70 && $competitiveFit < 55 && ! $this->isHighOrAboveRisk($riskLevel)) {
            return 'Casual Community Member';
        }

        if ($score('Respect for Casual Members') >= 75 && $score('Online Behavior') >= 75 && $score('Community Commitment') >= 65) {
            return 'Supportive Member';
        }

        if ($score('Drama Risk') >= 75 && $score('Online Behavior') >= 70 && $score('Community Commitment') >= 45 && $score('Community Commitment') <= 70 && $competitiveFit < 55) {
            return 'Quiet but Safe';
        }

        if ($competitiveFit >= 75 && ! $this->isLowOrBelowRisk($riskLevel)) {
            return 'Competitive but Risky';
        }

        if ($score('Drama Risk') < 50 || $score('Conflict Handling') < 50) {
            return 'Drama-Prone Member';
        }

        if ($score('Rule Acceptance') < 50) {
            return 'Rule-Resistant Member';
        }

        if (($this->isHighOrAboveRisk($riskLevel) && $communityFit < 60) || ($honestyStatus === 'Invalid' && $heavyRedFlags > 0)) {
            return 'Not Recommended';
        }

        return 'Community Candidate';
    }

    /**
     * @param  array<int, array<string, mixed>>  $suspiciousFlags
     */
    private function finalStatus(
        int $communityFit,
        int $competitiveFit,
        string $riskLevel,
        string $honestyStatus,
        int $heavyRedFlags,
        array $suspiciousFlags,
    ): string {
        if ($honestyStatus === 'Invalid' && $this->isHighOrAboveRisk($riskLevel)) {
            return 'Rejected';
        }

        if ($honestyStatus === 'Invalid') {
            return 'Retest';
        }

        if ($heavyRedFlags >= 2) {
            return 'Rejected';
        }

        if ($heavyRedFlags === 1 || $this->isHighOrAboveRisk($riskLevel)) {
            return 'Watchlist';
        }

        if ($honestyStatus === 'Questionable' || $this->hasSeverity($suspiciousFlags, 'medium')) {
            return 'Manual Review';
        }

        if ($competitiveFit >= 75 && $this->isMediumOrAboveRisk($riskLevel)) {
            return 'Manual Review';
        }

        if ($communityFit >= 70 && $competitiveFit < 55 && ! $this->isHighOrAboveRisk($riskLevel)) {
            return 'Accepted as Casual Member';
        }

        if ($communityFit >= 80 && $this->isLowOrBelowRisk($riskLevel) && $honestyStatus === 'Valid') {
            return 'Accepted';
        }

        if ($communityFit >= 65 && ! $this->isHighOrAboveRisk($riskLevel) && $honestyStatus === 'Valid') {
            return 'Accepted with Trial';
        }

        return 'Manual Review';
    }

    /**
     * @param  array<int, array<string, mixed>>  $flags
     */
    private function countFlags(array $flags, string $severity): int
    {
        return count(array_filter($flags, fn (array $flag): bool => $flag['severity'] === $severity));
    }

    /**
     * @param  array<int, array<string, mixed>>  $flags
     */
    private function hasSeverity(array $flags, string $severity): bool
    {
        return $this->countFlags($flags, $severity) > 0;
    }

    /**
     * @param  array<int, int>  $evidence
     * @return array<string, mixed>
     */
    private function flag(string $type, string $severity, string $message, array $evidence = []): array
    {
        return [
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'evidence' => $evidence,
        ];
    }
}
