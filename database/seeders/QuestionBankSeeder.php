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
        Question::query()->update(['display_order' => null]);

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

            $this->likert(77, 'Kalau voice/chat ramai, saya tetap memberi ruang orang lain selesai bicara atau mengetik.', 'Online Behavior', 'normal'),
            $this->likert(78, 'Saat suasana panas, saya kadang menaruh komentar singkat supaya obrolan makin seru.', 'Drama Risk', 'reverse'),

            $this->situational(79, 'Ada screenshot atau cerita tentang member lain yang belum jelas benar. Apa responsmu?', 'Drama Risk, Online Behavior', [
                'A' => 'Tidak ikut menyebarkan dan menunggu klarifikasi dari admin/moderator.',
                'B' => 'Menghindari pembahasan itu sampai ada info yang jelas.',
                'C' => 'Menanyakan detail ke beberapa orang karena penasaran.',
                'D' => 'Membagikan screenshot itu agar orang lain tahu duluan.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(80, 'Admin menegur cara kamu bercanda di public chat karena terasa mengganggu. Apa responsmu?', 'Accountability, Rule Acceptance', [
                'A' => 'Minta maaf, berhenti dulu, lalu tanya batas bercanda yang aman.',
                'B' => 'Mengurangi komentar dan lanjut dengan lebih hati-hati.',
                'C' => 'Menjelaskan panjang di public chat bahwa niatmu cuma bercanda.',
                'D' => 'Menyindir admin karena terlalu sensitif.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(81, 'Dalam event tim, teammate kamu lebih lambat dan membuat tim kalah. Apa responsmu?', 'Sportsmanship, Respect for Casual Members', [
                'A' => 'Tetap bilang good try dan bantu cari cara latihan bareng.',
                'B' => 'Diam dulu supaya tidak mengeluarkan komentar buruk.',
                'C' => 'Mengeluh bahwa timmu jadi berat karena dia.',
                'D' => 'Menyalahkan teammate itu di chat atau voice.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),
            $this->situational(82, 'Voice chat mulai ramai, orang saling potong, dan candaan terasa makin tajam. Apa responsmu?', 'Online Behavior, Toxicity Control', [
                'A' => 'Menurunkan tone, memberi ruang bicara, atau mengajak pindah topik.',
                'B' => 'Mute sebentar kalau suasananya terlalu ramai.',
                'C' => 'Ikut menaikkan candaan supaya tidak kalah seru.',
                'D' => 'Menekan orang lain dengan komentar pedas agar mereka diam.',
            ], ['A' => 4, 'B' => 3, 'C' => 1, 'D' => 0]),

            $this->likert(83, 'Saya hampir tidak pernah salah membaca suasana chat.', 'Honesty & Consistency', 'reverse_soft', true),
            $this->likert(84, 'Saya kadang perlu cek ulang maksud orang sebelum merespons.', 'Honesty & Consistency', 'normal_soft', true),
            $this->likert(85, 'Saya selalu bisa menerima teguran admin tanpa rasa kesal sedikit pun.', 'Honesty & Consistency', 'reverse_soft', true),
            $this->likert(86, 'Kalau baru ditegur, saya mungkin butuh sebentar sebelum menjawab dengan tenang.', 'Honesty & Consistency', 'normal_soft', true),

            $this->profileLikert(87, 'Saya menikmati event yang butuh koordinasi tim dan saling bantu.', 'team_style', 'T'),
            $this->profileLikert(88, 'Saya lebih nyaman mengejar progress sendiri sebelum banyak kerja sama tim.', 'team_style', 'I'),
            $this->profileLikert(89, 'Kompetisi membuat saya makin fokus untuk improve dan mengejar hasil terbaik.', 'competitive_style', 'D'),
            $this->profileLikert(90, 'Saya bisa kompetitif tanpa membuat lawan atau teammate merasa ditekan.', 'competitive_style', 'G'),
            $this->profileLikert(91, 'Saat ada drama kecil, saya lebih memilih tidak ikut memperpanjang suasana.', 'drama_resistance', 'L'),
            $this->profileLikert(92, 'Kalau ada masalah di server, saya cenderung cepat ingin membahasnya agar jelas.', 'drama_resistance', 'R'),
            $this->profileLikert(93, 'Saya bisa menerima feedback admin sebagai bahan memperbaiki cara main atau cara bicara.', 'feedback_style', 'F'),
            $this->profileLikert(94, 'Saya lebih nyaman menerima feedback kalau admin menjelaskan alasan dan contohnya.', 'feedback_style', 'J'),
            $this->profileLikert(95, 'Saya cukup nyaman ikut voice chat saat event atau main bareng.', 'interaction_style', 'V'),
            $this->profileLikert(96, 'Saya lebih nyaman berinteraksi lewat text chat daripada voice chat.', 'interaction_style', 'T'),
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
            'subcategory' => $this->subcategoryMap()[$questionNumber] ?? null,
            'public_options' => $questionType === 'likert'
                ? $this->likertPublicOptions()
                : $question['options'],
            'red_flag_options' => $questionType === 'situational' ? ['D'] : [],
            'risk_tags' => $this->riskTagMap()[$questionNumber] ?? [],
            'consistency_pair' => $consistency['pair'],
            'consistency_pair_id' => $consistency['pair_id'],
            'consistency_check' => $consistency['check'],
            'admin_notes' => $consistency['notes'],
            'research_basis' => $this->researchBasis($question),
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
     * @return array<int, string>
     */
    private function subcategoryMap(): array
    {
        return [
            77 => 'Voice-Chat Courtesy',
            78 => 'Provocation',
            79 => 'Rumor Containment',
            80 => 'Admin Feedback',
            81 => 'Team Sportsmanship',
            82 => 'Voice-Chat Interaction',
            83 => 'Social Self-Awareness',
            84 => 'Social Self-Awareness',
            85 => 'Feedback Self-Presentation',
            86 => 'Feedback Self-Regulation',
            87 => 'Team Style',
            88 => 'Team Style',
            89 => 'Competitive Style',
            90 => 'Competitive Style',
            91 => 'Drama Resistance',
            92 => 'Drama Resistance',
            93 => 'Admin Feedback Style',
            94 => 'Admin Feedback Style',
            95 => 'Voice/Chat Interaction Style',
            96 => 'Voice/Chat Interaction Style',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function riskTagMap(): array
    {
        return [
            78 => ['provocation', 'drama_amplification'],
            79 => ['rumor_spreading', 'drama_amplification'],
            80 => ['admin_resistance', 'public_debate'],
            81 => ['poor_sportsmanship', 'team_blame'],
            82 => ['voice_chat_disruption', 'provocation'],
        ];
    }

    private function researchBasis(array $question): string
    {
        if (($question['question_type'] ?? null) === 'situational') {
            return 'research-informed situational judgment item';
        }

        if (($question['is_consistency_item'] ?? false) === true) {
            return 'research-informed consistency and social desirability check';
        }

        if (($question['profile_axis'] ?? null) !== null) {
            return 'research-informed community style indicator';
        }

        return 'research-informed community behavior item';
    }

    /**
     * @return array{pair: array<int>, pair_id: string|null, check: string|null, notes: string|null}
     */
    private function consistencyMetadata(int $questionNumber): array
    {
        return match ($questionNumber) {
            54 => [
                'pair' => [56],
                'pair_id' => 'losing_reaction_realism',
                'check' => 'extreme_perfection_check',
                'notes' => 'Q54 sangat setuju + Q56 sangat tidak setuju = possible unrealistic perfection.',
            ],
            55 => [
                'pair' => [],
                'pair_id' => 'rule_reading_realism',
                'check' => 'unrealistic_perfection_check',
                'notes' => 'Q55 sangat setuju + banyak rule items rendah = contradiction.',
            ],
            56 => [
                'pair' => [54],
                'pair_id' => 'losing_reaction_realism',
                'check' => 'self_awareness_pair',
                'notes' => 'Pairs with Q54 for realistic response to losing.',
            ],
            57 => [
                'pair' => [],
                'pair_id' => 'impulse_realism',
                'check' => 'realistic_self_awareness',
                'notes' => 'Realistic self-awareness item for impulse control.',
            ],
            58 => [
                'pair' => [],
                'pair_id' => 'chat_misunderstanding_realism',
                'check' => 'impossible_perfection_check',
                'notes' => 'Q58 sangat setuju + conflict/drama risk rendah = check for impossible perfection.',
            ],
            59 => [
                'pair' => [21, 40, 48],
                'pair_id' => 'rule_acceptance_consistency',
                'check' => 'rule_acceptance_consistency',
                'notes' => 'Q59 sangat tidak setuju + rule acceptance items tinggi = contradiction.',
            ],
            60 => [
                'pair' => [21, 40, 48],
                'pair_id' => 'rule_explanation_consistency',
                'check' => 'rule_explanation_consistency',
                'notes' => 'Q60 sangat tidak setuju + Q40 sangat setuju = contradiction; also compare with Q21 and Q48.',
            ],
            83 => [
                'pair' => [84],
                'pair_id' => 'chat_tone_awareness',
                'check' => 'social_awareness_consistency',
                'notes' => 'Q83 sangat setuju + Q84 sangat tidak setuju = possible unrealistic tone-reading claim.',
            ],
            84 => [
                'pair' => [83],
                'pair_id' => 'chat_tone_awareness',
                'check' => 'social_awareness_consistency',
                'notes' => 'Pairs with Q83 for realistic chat interpretation.',
            ],
            85 => [
                'pair' => [86],
                'pair_id' => 'admin_feedback_realism',
                'check' => 'feedback_perfection_check',
                'notes' => 'Q85 sangat setuju + Q86 sangat tidak setuju = possible unrealistic feedback response.',
            ],
            86 => [
                'pair' => [85],
                'pair_id' => 'admin_feedback_realism',
                'check' => 'feedback_regulation_consistency',
                'notes' => 'Pairs with Q85 for realistic admin feedback response.',
            ],
            default => [
                'pair' => [],
                'pair_id' => null,
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
            'subcategory' => null,
            'scoring_direction' => $scoringDirection,
            'options' => null,
            'public_options' => null,
            'scoring_map' => null,
            'red_flag_options' => [],
            'risk_tags' => [],
            'consistency_pair' => [],
            'consistency_pair_id' => null,
            'consistency_check' => null,
            'admin_notes' => null,
            'research_basis' => null,
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
            'subcategory' => null,
            'scoring_direction' => 'normal',
            'options' => null,
            'public_options' => null,
            'scoring_map' => null,
            'red_flag_options' => [],
            'risk_tags' => [],
            'consistency_pair' => [],
            'consistency_pair_id' => null,
            'consistency_check' => null,
            'admin_notes' => null,
            'research_basis' => null,
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
            'subcategory' => null,
            'scoring_direction' => 'situational',
            'options' => $options,
            'public_options' => $options,
            'scoring_map' => $scoringMap,
            'red_flag_options' => ['D'],
            'risk_tags' => [],
            'consistency_pair' => [],
            'consistency_pair_id' => null,
            'consistency_check' => null,
            'admin_notes' => null,
            'research_basis' => null,
            'profile_axis' => null,
            'profile_pole' => null,
            'is_consistency_item' => false,
            'is_active' => true,
        ];
    }
}
