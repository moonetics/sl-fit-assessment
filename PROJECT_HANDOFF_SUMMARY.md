# Squad Limpul Community Fit Assessment - Project Handoff Summary

## Project Overview

Project ini adalah platform **Squad Limpul Community Fit Assessment**, sebuah assessment non-klinis untuk komunitas Roblox **Squad Limpul (SL)**.

Brand/community description:

> A chill Roblox community to hang out, race, and create awesome moments together.

Positioning utama:

- SL = Squad Limpul.
- Platform ini dipakai untuk menilai community fit, bukan untuk diagnosis psikologi, medis, atau mental health.
- Tone UI dibuat ringan, friendly, komunitas, dan tidak terasa seperti psikotes formal yang kaku.
- Footer wajib menampilkan `Managed Squad Limpul` dan tahun berjalan dari server.

Tech stack saat ini:

- Laravel 13
- Blade web flow
- Tailwind CSS via Vite
- SQLite untuk MVP lokal
- UUID string untuk primary key entity utama

## Latest State Snapshot - 2026-05-20

Status terakhir setelah SL Profile Code, Risk Reasoning, dan Admin Questions tab upgrade:

- MVP utama sudah berjalan: participant flow, scoring engine, admin dashboard, batch code generation, result review, manual override, audit log.
- Assessment sekarang berisi 76 soal aktif:
  - 45 Community Fit
  - 8 situational judgment
  - 7 honesty/consistency tersembunyi
  - 16 SL Profile Code items
- Admin result sekarang menampilkan `SL Profile Code`, `profile_name`, `profile_breakdown`, dan `risk_reasons`.
- `profile_breakdown` sekarang lebih detail: profile description, best fit, strengths, watchouts, admin guidance, axis confidence, axis scores, dan penjelasan tiap axis.
- SL Profile Code adalah indikator non-klinis admin-only untuk gaya komunitas, bukan MBTI, diagnosis, atau dasar otomatis final decision.
- Admin punya menu `Questions` read-only di `/admin/questions` untuk melihat semua 76 soal dan metadata scoring/profile lengkap.
- Homepage publik sudah profesional, memakai logo `public/logo/sl-logo.png`, dan tidak lagi menampilkan phase/roadmap/build-progress copy.
- Semua kode baru admin memakai format `SLFA-XXXX-XXXX`.
- Generate kode single dan batch wajib punya:
  - nama peserta
  - Discord User ID numerik
- Peserta tidak mengisi nama atau Discord data. Peserta hanya memasukkan access code, membaca instruksi, lalu klik `Start assessment`.
- Kode lama `CFA-*` masih bisa divalidasi jika ada di database, tetapi tidak bisa start jika belum punya `assigned_name` dan `assigned_discord_id`.
- Assessment tidak punya countdown, time limit, atau auto-submit. Durasi tetap dicatat di background untuk admin/scoring suspicious detection.
- Peserta boleh skip soal dan pindah lewat navigator 1-76, tetapi final submit tetap diblokir sampai semua soal terjawab.
- Navigator soal terakhir dibuat ulang memakai inline style eksplisit per nomor agar setiap nomor benar-benar tampil sebagai kotak.
- Verifikasi terakhir:
  - `php artisan test` => 56 tests passed, 404 assertions
  - `npm run build` => passed
  - `php artisan migrate --force` sudah menjalankan migration `2026_05_18_000007_add_sl_profile_results`
  - `php artisan db:seed --class=QuestionBankSeeder --force` menghasilkan 76 active questions di DB lokal
  - `php artisan view:clear` sudah dijalankan setelah perubahan admin result UI
- Server lokal terakhir aktif di `http://127.0.0.1:8003`.

## Reference Folder

Semua arah produk dan konten assessment bersumber dari folder:

`Reference/`

Referensi penting:

- `Reference/PRD_Community_Fit_Assessment.md`
- `Reference/Implementation_Checklist.md`
- `Reference/Data_Model_API_Draft.md`
- `Reference/Bank_Soal_Peserta.md`
- `Reference/Bank_Soal_Admin_Scoring.md`

## Phase 0 - Foundation & Product Direction

Status: selesai.

Yang sudah dilakukan:

- Landing page default Laravel diganti menjadi landing page Squad Limpul.
- Nama platform ditetapkan menjadi **Squad Limpul Community Fit Assessment**.
- Copy komunitas SL ditampilkan di halaman utama.
- Disclaimer non-klinis ditampilkan:
  - Assessment ini bukan psikotes klinis.
  - Assessment tidak digunakan untuk diagnosis psikologis, medis, atau mental health.
- Struktur awal halaman dibuat:
  - landing hero
  - input kode assessment
  - ringkasan info assessment
  - phase roadmap preview
  - footer
- Footer menggunakan dynamic year dari server:
  - `Managed Squad Limpul`
  - `{{ now()->year }}`

File utama:

- `resources/views/welcome.blade.php`

## Phase 1 - Data Model & System Architecture

Status: selesai.

Yang sudah dilakukan:

- Migration core assessment dibuat:
  - `admins`
  - `access_codes`
  - `participants`
  - `questions`
  - `answers`
  - `assessment_sessions`
  - `results`
  - `admin_notes`
  - `audit_logs`
- Eloquent model dibuat untuk semua tabel utama.
- Semua entity utama memakai UUID string.
- Status access code dikunci ke:
  - `Unused`
  - `In Progress`
  - `Completed`
  - `Expired`
  - `Locked`
- Relasi utama dibuat:
  - Admin punya banyak access code.
  - Access code punya maksimal satu participant.
  - Participant punya banyak answer/session/note.
  - Participant punya satu result.
  - Question punya banyak answer.
- Unique constraint penting dibuat:
  - `access_codes.code_hash`
  - `participants.access_code_id`
  - `answers(participant_id, question_id)`
  - `results.participant_id`
- API skeleton dibuat untuk participant dan admin.
- Seeder awal question bank dibuat.

File utama:

- `database/migrations/2026_05_18_000000_create_assessment_core_tables.php`
- `app/Models/Admin.php`
- `app/Models/AccessCode.php`
- `app/Models/Participant.php`
- `app/Models/Question.php`
- `app/Models/Answer.php`
- `app/Models/AssessmentSession.php`
- `app/Models/Result.php`
- `app/Models/AdminNote.php`
- `app/Models/AuditLog.php`
- `routes/api.php`
- `database/seeders/QuestionBankSeeder.php`
- `tests/Feature/PhaseOneArchitectureTest.php`

## Phase 2 - Question Bank & Assessment Content

Status: selesai.

Yang sudah dilakukan:

- 76 soal assessment dimasukkan ke seeder.
- Struktur metadata soal diperluas:
  - `display_order`
  - `public_options`
  - `red_flag_options`
  - `consistency_pair`
  - `consistency_check`
  - `admin_notes`
  - `profile_axis`
  - `profile_pole`
- `question_number` dipakai sebagai nomor internal admin/scoring.
- `display_order` dipakai sebagai urutan tampilan peserta.
- Urutan peserta mengikuti hidden placement:

```text
1-8, 54, 9-16, 55, 17-24, 56, 25-32, 57, 33-40, 58, 41-48, 59, 49-53, 60, 61-76
```

- Struktur konten:
  - 45 soal Community Fit
  - 8 soal situational
  - 7 soal honesty/consistency
  - 16 soal SL Profile Code
- Soal situational internal tetap `46-53`, tetapi tampil dalam participant order sesuai referensi.
- Semua situational item punya opsi A-D dan red flag option `D`.
- Honesty/consistency item tetap tampil sebagai soal biasa untuk peserta.
- Metadata internal tidak diekspos ke participant:
  - `category`
  - `scoring_direction`
  - `scoring_map`
  - `red_flag_options`
  - `consistency_pair`
  - `consistency_check`
  - `profile_axis`
  - `profile_pole`
- `Question::activeForParticipant()` dibuat untuk payload aman peserta.

File utama:

- `database/migrations/2026_05_18_000001_add_phase_two_metadata_to_questions_table.php`
- `app/Models/Question.php`
- `database/seeders/QuestionBankSeeder.php`
- `tests/Feature/PhaseTwoQuestionBankTest.php`

## Phase 3 - Participant MVP

Status: selesai.

Yang sudah dilakukan:

- Flow peserta end-to-end dibuat dengan Blade web flow.
- Route participant dibuat:
  - `GET /`
  - `POST /code/validate`
  - `GET /assessment/instructions`
  - `POST /assessment/start`
  - `GET /assessment/questions/{order}`
  - `POST /assessment/questions/{order}`
  - `GET /assessment/review`
  - `POST /assessment/submit`
  - `GET /assessment/completion`
- Landing page menjadi entry input kode.
- Validasi kode memakai `code_hash`, bukan plain code.
- Input kode dinormalisasi uppercase dan trim spasi.
- Status code handling:
  - `Unused` masuk instruksi.
  - `In Progress` resume ke progress berjalan.
  - `Completed`, `Expired`, `Locked` ditolak.
- Start assessment membuat atau memakai participant.
- Start assessment membuat assessment session.
- Status code berubah dari `Unused` ke `In Progress`.
- Halaman soal tampil satu soal per halaman.
- Likert memakai pilihan 1-4.
- Situational memakai pilihan A-D.
- Progress bar menampilkan total assessment dinamis, saat ini `Soal X dari 76`.
- Jawaban disimpan saat klik Next/Back.
- Review page menampilkan jumlah answered/missing.
- Final submit wajib semua 76 soal terjawab.
- Final submit mengubah access code menjadi `Completed`.
- Completion screen tidak menampilkan skor detail.

File utama:

- `app/Http/Controllers/ParticipantAssessmentController.php`
- `routes/web.php`
- `resources/views/components/layouts/participant.blade.php`
- `resources/views/assessment/instructions.blade.php`
- `resources/views/assessment/question.blade.php`
- `resources/views/assessment/review.blade.php`
- `resources/views/assessment/completion.blade.php`
- `tests/Feature/ParticipantMvpFlowTest.php`

## Phase 4 - Autosave, Resume & Session Safety

Status: selesai.

Yang sudah dilakukan:

- Endpoint autosave diimplementasikan:
  - `PUT /api/answers/autosave`
- Autosave memakai Laravel session dari Blade web flow.
- Jawaban otomatis tersimpan saat peserta memilih opsi.
- Step-save Next/Back tetap ada sebagai fallback jika JavaScript tidak berjalan.
- Halaman soal punya autosave banner:
  - `Menyimpan`
  - `Tersimpan`
  - `Offline`
  - `Gagal menyimpan`
- LocalStorage fallback dibuat dengan key berbasis:
  - `participant_id`
  - `display_order`
- Saat browser online kembali, local draft dikirim ulang.
- Saat halaman soal dibuka, local draft bisa dipakai untuk memilih UI value terbaru dan mencoba sync.
- Refresh halaman tetap memuat jawaban dari database.
- Resume kode `In Progress` diarahkan ke soal pertama yang belum dijawab.
- Session tracking ditambahkan:
  - `last_seen_at`
  - `resume_count`
  - `refresh_count`
  - `is_writer`
- Device tracking ringan ditambahkan:
  - memakai `X-SL-Device-ID`, cookie `sl_device_id`, atau session fallback
  - device ID di-hash bersama user agent
- Jika device unik untuk participant lebih dari 2:
  - access code menjadi `Locked`
  - `locked_reason` diisi
- Final submit dibuat idempotent dengan:
  - `submission_attempt_id`
- Penyimpanan jawaban dipusatkan dalam service:
  - `App\Services\AnswerRecorder`
- Autosave menolak:
  - kode `Completed`
  - kode `Expired`
  - kode `Locked`
  - jawaban invalid
  - session yang bukan writer aktif

File utama:

- `database/migrations/2026_05_18_000002_add_phase_four_session_safety_fields.php`
- `app/Services/AnswerRecorder.php`
- `app/Http/Controllers/Api/Participant/AnswerController.php`
- `app/Http/Controllers/ParticipantAssessmentController.php`
- `resources/views/assessment/question.blade.php`
- `resources/views/components/layouts/participant.blade.php`
- `tests/Feature/PhaseFourSessionSafetyTest.php`

## Phase 5 - Scoring Engine & Result Generation

Status: selesai.

Yang sudah dilakukan:

- `AssessmentScoringService` dibuat untuk menghitung:
  - normal scoring
  - reverse scoring
  - situational scoring
  - category score 0-100
  - Community Fit Score
  - Competitive Fit Score
  - Risk Score dan Risk Level
  - Honesty Status
  - red flags dan suspicious flags
  - Member Type
  - Final Status
- Scoring config dasar disimpan di `config/assessment_scoring.php`.
- Result dibuat otomatis saat final submit.
- Final submit tetap idempotent dan tidak membuat result duplikat.
- Completion screen peserta tetap tidak menampilkan skor/detail result.
- `results` ditambah:
  - `risk_score`
  - `auto_final_status`
  - `profile_code`
  - `profile_name`
  - `profile_breakdown`
  - `risk_reasons`

File utama:

- `app/Services/AssessmentScoringService.php`
- `config/assessment_scoring.php`
- `database/migrations/2026_05_18_000003_add_phase_five_result_fields.php`
- `tests/Feature/PhaseFiveScoringEngineTest.php`

## Phase 6 - Admin Dashboard MVP

Status: selesai.

Yang sudah dilakukan:

- Admin login/logout berbasis session Blade.
- Admin seeded dari env:
  - `ADMIN_EMAIL`
  - `ADMIN_PASSWORD`
  - fallback lokal `admin@squadlimpul.local` / `password`
- Admin dashboard dibuat di `/admin`.
- Admin bisa generate kode single.
- Admin bisa melihat list kode dan peserta.
- Admin bisa melihat question bank read-only lewat menu `Questions`.
- Questions page menampilkan summary active/community/situational/honesty/profile items, filter, dan metadata penuh untuk admin.
- Filter dashboard berdasarkan code status, risk level, final status, dan search.
- Detail peserta menampilkan score cards, SL Profile Code, risk reasons, category breakdown, red flags, suspicious activity, sessions, answer review, notes, dan final decision.
- Admin bisa add notes.
- Admin bisa override final status dengan alasan wajib.
- Lock/unlock/reset code tersedia.
- CSV export dasar tersedia.
- Audit log dibuat untuk aksi penting.

File utama:

- `app/Http/Controllers/Admin/*`
- `app/Http/Middleware/EnsureAdminAuthenticated.php`
- `resources/views/admin/*`
- `resources/views/components/layouts/admin.blade.php`
- `tests/Feature/PhaseSixAdminDashboardTest.php`

## Admin Questions Tab

Status: selesai.

Yang sudah dilakukan:

- Route admin baru:
  - `GET /admin/questions`
  - route name `admin.questions.index`
- Menu nav admin ditambah `Questions`.
- Halaman Questions bersifat read-only, tanpa edit/delete/reorder soal.
- Filter tersedia:
  - search teks/kategori/nomor
  - question type
  - category
  - scoring direction
  - profile axis
  - active/inactive
  - consistency only
  - red flag only
- Summary cards menampilkan:
  - Active Questions
  - Community Fit
  - Situational
  - Honesty Check
  - SL Profile
- Tabel admin menampilkan metadata lengkap:
  - display order
  - question number
  - text
  - type/category/scoring
  - public options
  - scoring map
  - red flag options
  - consistency pair/check/admin notes
  - profile axis/pole
  - active status
- Metadata tetap tidak diekspos ke participant payload/UI.

File utama:

- `app/Http/Controllers/Admin/QuestionController.php`
- `resources/views/admin/questions/index.blade.php`
- `resources/views/components/layouts/admin.blade.php`
- `routes/web.php`
- `tests/Feature/PhaseSixAdminDashboardTest.php`

## Phase 9 - Future Improvements Roadmap: Admin Ops Pack & Extension Points

Status: selesai untuk slice implementasi Phase 9A-9E ringan.

Yang sudah dilakukan:

- Batch generate kode:
  - tabel `code_batches`
  - relasi `access_codes.code_batch_id`
  - dashboard filter batch/source
  - audit log `BATCH_CODES_GENERATED`
- Configurable scoring thresholds:
  - tabel `assessment_settings`
  - screen admin `Scoring Settings`
  - scorer memakai setting DB dan fallback ke config
  - audit log `SCORING_SETTINGS_UPDATED`
- Report/export:
  - PDF-ready HTML report per participant
  - Markdown result summary per participant
- Interview module:
  - tabel `interviews`
  - admin bisa menyimpan interviewer, tanggal, pertanyaan, jawaban ringkas, dan outcome
  - participant detail menampilkan interview history
  - audit log `INTERVIEW_CREATED`
- Assessment robustness:
  - participant punya `question_order_snapshot`
  - urutan soal disnapshot stabil per participant
  - scoring tetap memakai `question_number`
  - participant UI tetap memakai order 1-76 tanpa metadata scoring/profile
  - telemetry jawaban ditambah untuk suspicious detection:
    - `answer_started_at`
    - `client_duration_seconds`
    - `visibility_change_count`
    - `offline_sync_count`
- Discord extension placeholder:
  - field optional `discord_user_id`
  - `discord_verified_at`
  - `discord_metadata`
  - `DiscordIdentityService` dibuat sebagai boundary tanpa auto role aktif
- Multi-community readiness:
  - tabel `communities`
  - optional `access_codes.community_id`
  - default Squad Limpul community seeded

File utama:

- `database/migrations/2026_05_18_000004_add_phase_nine_future_improvements.php`
- `app/Models/CodeBatch.php`
- `app/Models/AssessmentSetting.php`
- `app/Models/Interview.php`
- `app/Models/Community.php`
- `app/Services/AssessmentSettingsService.php`
- `app/Services/QuestionOrderService.php`
- `app/Services/DiscordIdentityService.php`
- `tests/Feature/PhaseNineFutureImprovementsTest.php`

## SL Profile Code & Risk Reasoning Upgrade

Status: selesai.

Yang sudah dilakukan:

- Assessment diperluas dari 60 menjadi 76 soal aktif.
- 16 soal baru dipakai untuk membaca gaya komunitas secara non-klinis:
  - Social Energy: `S` Social Connector / `Q` Quiet Steady
  - Play Drive: `R` Racer Drive / `C` Casual Community
  - Rule Style: `A` Admin-Aligned / `N` Needs Rationale
  - Conflict Style: `C` Calm Resolver / `E` Expressive Responder
- Scoring menghasilkan:
  - `profile_code`
  - `profile_name`
  - `profile_breakdown`
  - `risk_reasons`
- Profile code memakai 16 kombinasi nama:
  - `SRAC` Composed Race Captain
  - `SRAE` Hype Racer
  - `SRNC` Strategic Competitor
  - `SRNE` Expressive Challenger
  - `SCAC` Community Host
  - `SCAE` Energetic Hangout
  - `SCNC` Thoughtful Organizer
  - `SCNE` Social Challenger
  - `QRAC` Quiet Grinder
  - `QRAE` Focused Spark
  - `QRNC` Analytical Runner
  - `QRNE` Independent Racer
  - `QCAC` Steady Supporter
  - `QCAE` Warm Casual
  - `QCNC` Calm Observer
  - `QCNE` Independent Casual
- Profile questions dihitung terpisah dari Community Fit, Competitive Fit, Risk Score, Honesty Status, dan Final Status.
- Tie-break profile memakai pole yang lebih netral/aman: `Q`, `C`, `A`, `C`.
- Profile breakdown mencakup deskripsi profil keseluruhan, best fit, strengths, watchouts, admin guidance, axis confidence, dan skor axis.
- Risk reasons dibuat dari risk score, kategori risiko rendah, red flags, dan suspicious flags.
- Admin participant detail menampilkan panel `SL Profile Code`, `Profile breakdown`, dan `Why this risk level?`.
- PDF-ready report, Markdown report, dan CSV export ikut menampilkan profile summary.
- Participant completion page tetap tidak menampilkan score, profile, risk detail, atau red flag.

File utama:

- `database/migrations/2026_05_18_000007_add_sl_profile_results.php`
- `database/seeders/QuestionBankSeeder.php`
- `app/Services/AssessmentScoringService.php`
- `resources/views/admin/participants/show.blade.php`
- `resources/views/admin/reports/show.blade.php`
- `resources/views/admin/reports/markdown.blade.php`
- `tests/Feature/PhaseFiveScoringEngineTest.php`

## UX & Code Generation Polish - Professional Portal, SL Logo, Named SLFA Codes

Status: selesai.

Yang sudah dilakukan:

- Homepage diubah menjadi portal assessment profesional.
- Section roadmap/build phases/MVP scope dihapus dari homepage publik.
- Logo Squad Limpul memakai asset:
  - `public/logo/sl-logo.png`
- Header participant dan admin juga memakai logo PNG.
- Format kode baru untuk semua kode yang dibuat admin:
  - `SLFA-XXXX-XXXX`
- Kode lama `CFA-*` tetap bisa divalidasi jika sudah tersimpan di database.
- `access_codes` ditambah field:
  - `assigned_name`
  - `assigned_discord_id`
- Single code generation mewajibkan participant full name dan Discord User ID.
- Batch generate memakai CSV textarea:
  - satu peserta per baris
  - format `Nama Lengkap, DiscordUserID`
  - jumlah kode mengikuti jumlah baris valid
- Dashboard admin menampilkan assigned name dan Discord User ID untuk kode yang belum mulai.
- Batch page menampilkan kode, assigned name, dan Discord User ID.
- Halaman instruksi peserta menjadi personalized welcome page:
  - menyapa nama peserta jika `assigned_name` tersedia
  - nama dari admin dipakai otomatis sebagai `participants.display_name`
  - Discord User ID dari admin dipakai otomatis sebagai `participants.discord_user_id`
  - peserta tidak mengisi nama atau Discord data
  - kode lama tanpa data peserta lengkap tidak bisa start dan diarahkan menghubungi admin

File utama:

- `database/migrations/2026_05_18_000005_add_assigned_name_to_access_codes.php`
- `database/migrations/2026_05_18_000006_add_assigned_discord_id_to_access_codes.php`
- `resources/views/welcome.blade.php`
- `resources/views/assessment/instructions.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/batches/index.blade.php`
- `tests/Feature/ProfessionalPortalAndNamedCodesTest.php`

## Participant UX Polish - Skip Navigation, Glossary Notes, No Discord Username

Status: selesai.

Yang sudah dilakukan:

- Instruction/welcome page diperbarui:
  - eyebrow menjadi `WELCOME TO SQUAD LIMPUL FIT ASSESSMENT`
  - copy yang menjelaskan nama disiapkan admin dihapus
  - copy seperti `Hanya untuk admin` dihapus dari participant-facing UI
- Input Discord username dihapus dari participant flow.
- Karena kolom `participants.discord_username` masih non-null untuk kompatibilitas DB, sistem menyimpan Discord User ID dari admin sebagai fallback internal.
- Halaman soal diperbarui:
  - pilihan jawaban menjadi selectable cards yang lebih besar dan lebih jelas
  - native radio tetap ada untuk accessibility, tetapi disembunyikan secara visual
  - selected answer punya border/background yang lebih kuat
  - autosave status tidak lagi menggeser layout
  - status hanya tampil subtle sebagai `Tersimpan`, `Offline`, atau `Gagal menyimpan`
- Peserta sekarang bisa skip soal:
  - `Next` bisa lanjut tanpa jawaban
  - tombol `Lewati dulu` muncul untuk soal yang belum dijawab
  - jawaban kosong tidak membuat row answer
  - final submit tetap wajib semua soal terjawab
- Navigator soal ditambahkan:
  - grid 1-76
  - hijau untuk sudah terisi
  - putih untuk belum terisi
  - current question diberi highlight gelap
  - peserta bisa klik nomor untuk pindah soal
- Navigator soal terakhir diperbaiki ulang:
  - panel kanan dilebarkan menjadi 250px
  - grid fixed 5 kolom
  - setiap nomor diberi inline style langsung pada anchor
  - ukuran kotak 38x38px
  - angka center dengan `inline-flex`
  - border 2px agar tidak terlihat seperti teks biasa
- Review page menampilkan daftar soal kosong sebagai shortcut.
- Estimasi waktu tidak ditampilkan di UI.
- Tidak ada countdown timer, time limit, atau auto-submit.
- Timing tetap berjalan di background melalui telemetry/durasi session.
- Glossary note ditambahkan untuk istilah yang mungkin kurang familiar:
  - Trash talk
  - GG
  - Flame war
  - Spam
  - Mention
  - Leaderboard
  - Trial
  - Moderator

File utama:

- `config/assessment_glossary.php`
- `app/Http/Controllers/ParticipantAssessmentController.php`
- `resources/views/assessment/instructions.blade.php`
- `resources/views/assessment/question.blade.php`
- `resources/views/assessment/review.blade.php`
- `tests/Feature/ParticipantMvpFlowTest.php`

## Current Important Routes

Participant web routes:

```text
GET  /
POST /code/validate
GET  /assessment/instructions
POST /assessment/start
GET  /assessment/questions/{order}
POST /assessment/questions/{order}
GET  /assessment/review
POST /assessment/submit
GET  /assessment/completion
```

Participant API routes:

```text
POST /api/code/validate
POST /api/assessment/start
GET  /api/assessment/current
PUT  /api/answers/autosave
POST /api/assessment/submit
GET  /api/assessment/completion
```

Admin API skeleton routes:

```text
POST  /api/admin/login
POST  /api/admin/codes
GET   /api/admin/codes
PATCH /api/admin/codes/{id}/reset
PATCH /api/admin/codes/{id}/lock
PATCH /api/admin/codes/{id}/unlock
GET   /api/admin/participants
GET   /api/admin/participants/{id}/result
POST  /api/admin/participants/{id}/notes
PATCH /api/admin/participants/{id}/final-status
GET   /api/admin/export/results.csv
GET   /api/admin/audit-logs
```

Admin web routes:

```text
GET /admin
GET /admin/questions
GET /admin/batches
GET /admin/scoring-settings
GET /admin/audit-logs
GET /admin/export/results.csv
```

## Current Verification Status

Last known successful checks:

```bash
php artisan test
```

Result:

```text
56 tests passed
404 assertions
```

Build:

```bash
npm run build
```

Result:

```text
Vite build passed
```

Migration:

```bash
php artisan migrate --force
```

Result:

```text
Latest MVP migrations include scoring, admin ops, assigned names, and assigned Discord User IDs.
Latest local migration also includes SL Profile Code and risk reasons result fields.
```

Local server was started at:

```text
http://127.0.0.1:8003
```

Port may be different if started again later.

## How To Run Locally

Install dependencies if needed:

```bash
composer install
npm install
```

Prepare database:

```bash
php artisan migrate --seed
```

Run tests:

```bash
php artisan test
```

Run frontend build:

```bash
npm run build
```

Clear compiled Blade views after UI/view changes:

```bash
php artisan view:clear
```

Start local server:

```bash
php artisan serve
```

## Manual Testing Notes

Admin dashboard/code generator tersedia di `/admin`.

Default local admin seed:

```text
admin@squadlimpul.local / password
```

Access code validation memakai hash:

```php
hash('sha256', strtoupper(preg_replace('/\s+/', '', trim($plainCode))))
```

Contoh plain code:

```text
SLFA-XXXX-XXXX
```

Yang harus diperhatikan saat manual test:

- Generate kode baru wajib menyertakan nama peserta dan Discord User ID.
- Batch generate memakai format `Nama Lengkap, DiscordUserID`.
- Kode `Unused` masuk ke instruksi.
- Peserta tidak mengisi nama atau Discord data.
- Setelah start, kode berubah menjadi `In Progress`.
- Refresh halaman soal tidak menghapus jawaban.
- Pilih opsi harus memunculkan autosave status.
- Jika offline, draft disimpan di localStorage.
- Peserta bisa skip soal, tetapi final submit tetap wajib semua soal terjawab.
- Total assessment saat ini 76 soal.
- Navigator soal harus terlihat sebagai kotak 38x38px:
  - current = hitam
  - terisi = teal/hijau
  - belum terisi = putih dengan border
- Kode `Completed` tidak bisa dipakai ulang.
- Completion page tidak menampilkan skor.

## What Is Not Implemented Yet

Belum dibuat:

- Admin authentication production.
- Hard conflict resolver untuk multi-device.
- Full offline sync beyond browser localStorage.
- Discord OAuth nyata.
- Auto role Discord.
- PDF binary generator/download otomatis.
- Full multi-community tenant switching dan branding runtime.
- Advanced anti-cheat fingerprinting di luar telemetry dasar.
- Runtime editor lengkap untuk scoring weights/profile per community.

## Suggested Next Phase

Rekomendasi fase berikutnya:

## Phase 10A - Admin Insight Summary

Tujuan:

- Membuat hasil assessment lebih cepat dibaca admin.
- Menambahkan ringkasan strengths, concerns, onboarding recommendation, dan interview prompts berbasis hasil yang sudah ada.

Yang disarankan:

- Tambahkan field result admin-only:
  - `strengths`
  - `concerns`
  - `onboarding_recommendation`
  - `interview_prompts`
- Insight dibuat dari category scores, risk reasons, red flags, suspicious flags, SL Profile Code, dan final status.
- Insight tidak mengubah scoring atau final decision.

## Phase 10B - Production Hardening & Integration

Tujuan:

- Menguatkan fitur yang sudah ada sebelum integrasi eksternal besar.
- Menyiapkan admin auth production, Discord integration nyata, dan deployment.

Yang perlu dikerjakan:

- Admin auth production hardening.
- Policy/permission untuk owner/admin/moderator.
- Discord OAuth dan optional auto role setelah approval admin.
- PDF binary export jika dibutuhkan.
- Backup/retention policy.
- Deployment checklist.

## Notes For Future Assistant

Saat melanjutkan project ini:

- Jangan expose metadata scoring ke participant UI.
- Jangan tampilkan score/result detail ke peserta.
- Jangan tampilkan SL Profile Code, risk reasons, red flags, atau profile breakdown ke peserta.
- Jangan pakai SL Profile Code sebagai diagnosis/personality klinis atau sebagai dasar otomatis final decision.
- Gunakan `question_number` untuk scoring internal.
- Gunakan `display_order` untuk UI peserta.
- Pertahankan status access code baku:
  - `Unused`
  - `In Progress`
  - `Completed`
  - `Expired`
  - `Locked`
- Pertahankan SQLite compatibility untuk MVP.
- Jika menambah kolom, buat migration baru, jangan ubah migration lama yang sudah ada.
- Jalankan minimal:

```bash
php artisan test
npm run build
```

sebelum menyatakan phase selesai.
