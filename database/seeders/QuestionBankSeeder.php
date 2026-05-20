<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    /**
     * Seed the assessment question bank from the admin/scoring reference.
     */
    public function run(): void
    {
        foreach ($this->questions() as $question) {
            $question = array_merge(
                $question,
                $this->phaseTwoMetadata($question),
            );

            Question::updateOrCreate(
                ['question_number' => $question['question_number']],
                $question,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questions(): array
    {
        return [
            $this->likert(1, 'Saya menjaga bahasa chat agar tetap sopan walaupun sedang kesal.', 'Online Behavior', 'normal'),
            $this->likert(2, 'Saya merasa spam atau mention berulang itu wajar kalau ingin cepat diperhatikan.', 'Online Behavior', 'reverse'),
            $this->likert(3, 'Saya membaca aturan channel sebelum bertanya atau bertindak.', 'Rule Acceptance', 'normal'),
            $this->likert(4, 'Kalau admin menegur saya, saya akan mencoba memahami alasannya dulu.', 'Rule Acceptance', 'normal'),
            $this->likert(5, 'Saya mudah terpancing membalas ejekan dengan ejekan juga.', 'Toxicity Control', 'reverse'),
            $this->likert(6, 'Saya bisa bercanda tanpa menghina kemampuan bermain orang lain.', 'Toxicity Control', 'normal'),
            $this->likert(7, 'Jika saya kalah race, saya tetap bisa mengucapkan GG atau menerima hasilnya.', 'Sportsmanship', 'normal'),
            $this->likert(8, 'Kekalahan biasanya membuat saya ingin menyalahkan map, lag, atau pemain lain di chat.', 'Sportsmanship', 'reverse'),
            $this->likert(9, 'Saya suka meningkatkan waktu/skill obby tanpa merusak suasana komunitas.', 'Competitive Attitude', 'normal'),
            $this->likert(10, 'Menjadi yang tercepat lebih penting daripada menjaga hubungan dengan member lain.', 'Competitive Attitude', 'reverse'),
            $this->likert(11, 'Member casual tetap punya tempat penting di komunitas walaupun tidak ikut race.', 'Respect for Casual Members', 'normal'),
            $this->likert(12, 'Saya merasa member yang lambat sebaiknya tidak ikut event komunitas.', 'Respect for Casual Members', 'reverse'),
            $this->likert(13, 'Saat ada konflik, saya lebih memilih menyelesaikannya lewat jalur admin/moderator daripada memperpanjang di public chat.', 'Conflict Handling', 'normal'),
            $this->likert(14, 'Kalau saya merasa benar, saya akan terus membahas masalah itu di channel publik sampai orang lain mengaku salah.', 'Conflict Handling', 'reverse'),
            $this->likert(15, 'Saya bisa mengakui kesalahan jika saya memang melanggar aturan.', 'Accountability', 'normal'),
            $this->likert(16, 'Kalau banyak orang juga melanggar aturan, saya merasa tidak perlu bertanggung jawab.', 'Accountability', 'reverse'),
            $this->likert(17, 'Saya tidak suka membawa masalah pribadi ke banyak channel Discord.', 'Drama Risk', 'normal'),
            $this->likert(18, 'Kalau ada konflik, saya biasanya mencari dukungan teman agar pihak lain terlihat salah.', 'Drama Risk', 'reverse'),
            $this->likert(19, 'Saya tertarik ikut komunitas bukan hanya untuk menang, tetapi juga untuk suasana dan teman main.', 'Community Commitment', 'normal'),
            $this->likert(20, 'Saya kemungkinan akan keluar kalau saya tidak cepat dikenal atau tidak masuk leaderboard.', 'Community Commitment', 'reverse'),
            $this->likert(21, 'Saya bisa menerima keputusan admin walaupun tidak selalu sesuai keinginan saya.', 'Rule Acceptance', 'normal'),
            $this->likert(22, 'Menurut saya, aturan server boleh diabaikan kalau sedang bercanda.', 'Rule Acceptance', 'reverse'),
            $this->likert(23, 'Saya menghindari kata-kata yang bisa memancing flame war.', 'Toxicity Control', 'normal'),
            $this->likert(24, 'Trash talk keras adalah bagian normal dari kompetisi online.', 'Toxicity Control', 'reverse'),
            $this->likert(25, 'Saya bisa memberi tips kepada pemain baru tanpa membuat mereka merasa bodoh.', 'Respect for Casual Members', 'normal'),
            $this->likert(26, 'Saya hanya mau bermain dengan orang yang skill-nya setara atau lebih tinggi dari saya.', 'Respect for Casual Members', 'reverse'),
            $this->likert(27, 'Saya tetap menghormati pemenang race walaupun saya merasa sebenarnya bisa menang.', 'Sportsmanship', 'normal'),
            $this->likert(28, 'Jika saya menang, saya boleh sedikit merendahkan lawan karena itu bagian dari hype.', 'Sportsmanship', 'reverse'),
            $this->likert(29, 'Saya senang mengikuti event kompetitif selama aturannya jelas.', 'Competitive Attitude', 'normal'),
            $this->likert(30, 'Saya akan protes keras jika aturan race membuat saya tidak diuntungkan.', 'Competitive Attitude', 'reverse'),
            $this->likert(31, 'Saya berpikir sebelum mengirim pesan saat emosi.', 'Online Behavior', 'normal'),
            $this->likert(32, 'Saya sering menyesal setelah mengirim pesan saat marah.', 'Online Behavior', 'reverse'),
            $this->likert(33, 'Saya bersedia meminta maaf jika tindakan saya membuat suasana server tidak nyaman.', 'Accountability', 'normal'),
            $this->likert(34, 'Saya sulit meminta maaf kalau saya merasa niat saya sebenarnya bercanda.', 'Accountability', 'reverse'),
            $this->likert(35, 'Jika ada rumor tentang member lain, saya tidak langsung menyebarkannya.', 'Drama Risk', 'normal'),
            $this->likert(36, 'Saya suka tahu drama terbaru di server dan ikut membahasnya.', 'Drama Risk', 'reverse'),
            $this->likert(37, 'Saya dapat menerima bahwa tidak semua member ingin race serius.', 'Respect for Casual Members', 'normal'),
            $this->likert(38, 'Komunitas obby seharusnya memprioritaskan racer cepat dibanding member casual.', 'Respect for Casual Members', 'reverse'),
            $this->likert(39, 'Saya mau mengikuti masa trial jika itu membantu admin mengenal perilaku saya.', 'Community Commitment', 'normal'),
            $this->likert(40, 'Saya tidak suka jika komunitas terlalu banyak aturan onboarding.', 'Rule Acceptance', 'reverse'),
            $this->likert(41, 'Jika saya berbeda pendapat, saya bisa menyampaikan dengan tenang.', 'Conflict Handling', 'normal'),
            $this->likert(42, 'Saya sering merasa perlu membuktikan bahwa saya benar dalam debat online.', 'Conflict Handling', 'reverse'),
            $this->likert(43, 'Saya bisa aktif di komunitas tanpa mencari perhatian berlebihan.', 'Community Commitment', 'normal'),
            $this->likert(44, 'Saya merasa admin harus memberi perlakuan khusus kepada racer yang punya skill tinggi.', 'Rule Acceptance', 'reverse'),
            $this->likert(45, 'Saya memahami bahwa skill tinggi tidak memberi izin untuk bersikap toxic.', 'Toxicity Control', 'normal'),

            $this->situational(46, 'Kamu kalah tipis dalam race karena melakukan kesalahan di obstacle terakhir. Apa respons terbaikmu?', 'Sportsmanship, Accountability', [
                'A' => 'GG, aku salah di akhir. Next aku coba improve.',
                'B' => 'Diam saja dan mencoba lagi tanpa komentar negatif.',
                'C' => 'Menyindir map atau lag di chat.',
                'D' => 'Menuduh pemenang beruntung atau curang tanpa bukti.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(47, 'Member casual bertanya hal dasar yang menurutmu mudah. Apa yang kamu lakukan?', 'Respect for Casual Members, Toxicity Control', [
                'A' => 'Menjawab singkat dan sopan, atau arahkan ke guide.',
                'B' => 'Membiarkan member lain menjawab.',
                'C' => 'Menjawab sambil menyindir "masa gitu aja nggak tahu".',
                'D' => 'Mengajak orang lain menertawakan dia.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(48, 'Admin membuat aturan baru untuk race mingguan yang tidak kamu sukai. Apa responsmu?', 'Rule Acceptance, Conflict Handling', [
                'A' => 'Bertanya alasan aturan dengan sopan dan memberi masukan.',
                'B' => 'Mengikuti dulu sambil melihat hasilnya.',
                'C' => 'Mengeluh di public chat berkali-kali.',
                'D' => 'Mengajak member lain melawan aturan admin.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(49, 'Ada member yang memancing emosi kamu di Discord. Apa responsmu?', 'Toxicity Control, Conflict Handling', [
                'A' => 'Tidak membalas provokasi dan lapor moderator jika perlu.',
                'B' => 'Mute/abaikan sementara.',
                'C' => 'Membalas dengan sindiran agar dia berhenti.',
                'D' => 'Membalas kasar agar dia kapok.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(50, 'Kamu menang race dan banyak member memujimu. Apa responsmu?', 'Sportsmanship, Online Behavior', [
                'A' => 'Berterima kasih dan tetap menghargai peserta lain.',
                'B' => 'Merayakan secukupnya tanpa merendahkan.',
                'C' => 'Menulis "ez" atau "terlalu gampang" di chat.',
                'D' => 'Mengejek pemain yang kalah.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(51, 'Kamu melihat temanmu melanggar aturan kecil saat event. Apa yang kamu lakukan?', 'Accountability, Rule Acceptance', [
                'A' => 'Mengingatkan secara personal atau lapor admin jika perlu.',
                'B' => 'Tidak ikut melanggar dan membiarkan admin menangani.',
                'C' => 'Ikut melanggar karena temanmu juga melakukannya.',
                'D' => 'Membantu menutupi pelanggaran.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(52, 'Ada rumor bahwa seorang member menyebabkan drama. Kamu belum tahu faktanya. Apa responsmu?', 'Drama Risk, Online Behavior', [
                'A' => 'Tidak menyebarkan dan menunggu info dari admin/moderator.',
                'B' => 'Menghindari pembahasan rumor.',
                'C' => 'Bertanya ke banyak orang untuk mencari detail.',
                'D' => 'Menyebarkan rumor ke channel lain.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(53, 'Kamu merasa tidak cocok dengan satu moderator. Apa tindakanmu?', 'Rule Acceptance, Conflict Handling', [
                'A' => 'Tetap mengikuti aturan dan menyampaikan concern secara private/resmi.',
                'B' => 'Menghindari interaksi tidak perlu tetapi tetap sopan.',
                'C' => 'Menyindir moderator itu di public chat.',
                'D' => 'Mengajak member lain untuk tidak menghormati moderator tersebut.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),

            $this->likert(54, 'Saya tidak pernah merasa kesal saat kalah dalam game online.', 'Honesty & Consistency', 'reverse_soft', true),
            $this->likert(55, 'Saya selalu membaca semua aturan server secara lengkap sebelum mengirim pesan pertama.', 'Honesty & Consistency', 'reverse_soft', true),
            $this->likert(56, 'Saya kadang perlu waktu untuk menenangkan diri setelah race yang mengecewakan.', 'Honesty & Consistency', 'normal_soft', true),
            $this->likert(57, 'Saya pernah ingin membalas komentar yang terasa menyebalkan, walaupun akhirnya tidak saya lakukan.', 'Honesty & Consistency', 'normal_soft', true),
            $this->likert(58, 'Saya tidak pernah sekalipun salah paham dengan orang lain di chat.', 'Honesty & Consistency', 'reverse_soft', true),
            $this->likert(59, 'Saya bisa berubah pikiran setelah admin menjelaskan aturan dengan baik.', 'Honesty & Consistency', 'normal', true),
            $this->likert(60, 'Jika saya merasa aturan tidak adil, saya mungkin butuh penjelasan dulu sebelum menerimanya.', 'Honesty & Consistency', 'normal_soft', true),

            $this->profileLikert(61, 'Saya mudah memulai obrolan ringan dengan member baru di server.', 'social', 'S'),
            $this->profileLikert(62, 'Saya lebih nyaman mengamati suasana dulu sebelum ikut banyak ngobrol.', 'social', 'Q'),
            $this->profileLikert(63, 'Saya senang membantu member baru merasa diterima saat pertama masuk.', 'social', 'S'),
            $this->profileLikert(64, 'Saya tetap bisa menjadi member yang baik meskipun tidak terlalu sering muncul di chat.', 'social', 'Q'),
            $this->profileLikert(65, 'Leaderboard dan improve time membuat saya lebih semangat bermain.', 'play_drive', 'R'),
            $this->profileLikert(66, 'Saya lebih mencari suasana main santai daripada mengejar posisi tercepat.', 'play_drive', 'C'),
            $this->profileLikert(67, 'Saya suka mengulang run untuk memperbaiki detail kecil dalam permainan.', 'play_drive', 'R'),
            $this->profileLikert(68, 'Bagi saya, komunitas tetap seru walaupun saya tidak ikut race serius.', 'play_drive', 'C'),
            $this->profileLikert(69, 'Saya biasanya mengikuti arahan admin dulu sambil memahami tujuannya.', 'rule_style', 'A'),
            $this->profileLikert(70, 'Saya lebih nyaman mengikuti aturan jika alasan di baliknya dijelaskan dengan jelas.', 'rule_style', 'N'),
            $this->profileLikert(71, 'Kalau ada aturan baru, saya bisa menyesuaikan diri tanpa banyak debat.', 'rule_style', 'A'),
            $this->profileLikert(72, 'Saya suka memberi masukan jika aturan terasa kurang jelas atau kurang adil.', 'rule_style', 'N'),
            $this->profileLikert(73, 'Saat emosi, saya cenderung menunggu tenang dulu sebelum merespons.', 'conflict_style', 'C'),
            $this->profileLikert(74, 'Jika ada masalah, saya cenderung langsung menyampaikan apa yang saya rasakan.', 'conflict_style', 'E'),
            $this->profileLikert(75, 'Saya lebih memilih membahas konflik secara private atau lewat moderator.', 'conflict_style', 'C'),
            $this->profileLikert(76, 'Saya mudah menunjukkan ketidaksetujuan ketika suasana terasa tidak adil.', 'conflict_style', 'E'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function phaseTwoMetadata(array $question): array
    {
        $questionNumber = $question['question_number'];
        $questionType = $question['question_type'];
        $consistency = $this->consistencyMetadata($questionNumber);

        return [
            'display_order' => $this->displayOrderMap()[$questionNumber],
            'public_options' => $questionType === 'likert'
                ? $this->likertPublicOptions()
                : $question['options'],
            'red_flag_options' => $questionType === 'situational' ? ['D'] : [],
            'consistency_pair' => $consistency['pair'],
            'consistency_check' => $consistency['check'],
            'admin_notes' => $consistency['notes'],
            'profile_axis' => $question['profile_axis'] ?? null,
            'profile_pole' => $question['profile_pole'] ?? null,
        ];
    }

    /**
     * @return array<int, int>
     */
    private function displayOrderMap(): array
    {
        $sequence = [
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
            ...range(49, 53),
            60,
            ...range(61, 76),
        ];

        $displayOrder = [];

        foreach ($sequence as $index => $questionNumber) {
            $displayOrder[$questionNumber] = $index + 1;
        }

        return $displayOrder;
    }

    /**
     * @return array<int, string>
     */
    private function likertPublicOptions(): array
    {
        return [
            1 => 'Sangat tidak setuju',
            2 => 'Tidak setuju',
            3 => 'Setuju',
            4 => 'Sangat setuju',
        ];
    }

    /**
     * @return array{pair: array<int>, check: string|null, notes: string|null}
     */
    private function consistencyMetadata(int $questionNumber): array
    {
        return match ($questionNumber) {
            54 => [
                'pair' => [56],
                'check' => 'extreme_perfection_check',
                'notes' => 'Q54 sangat setuju + Q56 sangat tidak setuju = possible unrealistic perfection.',
            ],
            55 => [
                'pair' => [],
                'check' => 'unrealistic_perfection_check',
                'notes' => 'Q55 sangat setuju + banyak rule items rendah = contradiction.',
            ],
            56 => [
                'pair' => [54],
                'check' => 'self_awareness_pair',
                'notes' => 'Pairs with Q54 for realistic response to losing.',
            ],
            57 => [
                'pair' => [],
                'check' => 'realistic_self_awareness',
                'notes' => 'Realistic self-awareness item for impulse control.',
            ],
            58 => [
                'pair' => [],
                'check' => 'impossible_perfection_check',
                'notes' => 'Q58 sangat setuju + conflict/drama risk rendah = check for impossible perfection.',
            ],
            59 => [
                'pair' => [21, 40, 48],
                'check' => 'rule_acceptance_consistency',
                'notes' => 'Q59 sangat tidak setuju + rule acceptance items tinggi = contradiction.',
            ],
            60 => [
                'pair' => [21, 40, 48],
                'check' => 'rule_explanation_consistency',
                'notes' => 'Q60 sangat tidak setuju + Q40 sangat setuju = contradiction; also compare with Q21 and Q48.',
            ],
            default => [
                'pair' => [],
                'check' => null,
                'notes' => null,
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function likert(
        int $number,
        string $text,
        string $category,
        string $scoringDirection,
        bool $isConsistencyItem = false,
    ): array {
        return [
            'question_number' => $number,
            'text' => $text,
            'question_type' => 'likert',
            'category' => $category,
            'scoring_direction' => $scoringDirection,
            'options' => null,
            'public_options' => null,
            'scoring_map' => null,
            'red_flag_options' => [],
            'consistency_pair' => [],
            'consistency_check' => null,
            'admin_notes' => null,
            'profile_axis' => null,
            'profile_pole' => null,
            'is_consistency_item' => $isConsistencyItem,
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profileLikert(
        int $number,
        string $text,
        string $axis,
        string $pole,
    ): array {
        return [
            'question_number' => $number,
            'text' => $text,
            'question_type' => 'likert',
            'category' => 'SL Profile',
            'scoring_direction' => 'normal',
            'options' => null,
            'public_options' => null,
            'scoring_map' => null,
            'red_flag_options' => [],
            'consistency_pair' => [],
            'consistency_check' => null,
            'admin_notes' => null,
            'profile_axis' => $axis,
            'profile_pole' => $pole,
            'is_consistency_item' => false,
            'is_active' => true,
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @param  array<string, int>  $scoringMap
     * @return array<string, mixed>
     */
    private function situational(
        int $number,
        string $text,
        string $category,
        array $options,
        array $scoringMap,
    ): array {
        return [
            'question_number' => $number,
            'text' => $text,
            'question_type' => 'situational',
            'category' => $category,
            'scoring_direction' => 'situational',
            'options' => $options,
            'public_options' => $options,
            'scoring_map' => $scoringMap,
            'red_flag_options' => ['D'],
            'consistency_pair' => [],
            'consistency_check' => null,
            'admin_notes' => null,
            'profile_axis' => null,
            'profile_pole' => null,
            'is_consistency_item' => false,
            'is_active' => true,
        ];
    }
}
