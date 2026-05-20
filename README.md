# Squad Limpul Community Fit Assessment

Platform assessment non-klinis untuk komunitas Roblox **Squad Limpul (SL)**.

Squad Limpul adalah komunitas Roblox yang santai untuk hang out, race, dan membuat momen seru bersama. Project ini membantu admin menilai **community fit** calon member secara lebih rapi, konsisten, dan terdokumentasi tanpa memosisikan assessment sebagai psikotes klinis, diagnosis psikologis, atau penilaian mental health.

## Tujuan Platform

- Membantu admin membaca kecocokan calon member dengan budaya komunitas SL.
- Menyediakan flow assessment yang ringan, friendly, dan tidak terasa seperti psikotes formal.
- Menjaga hasil scoring, risk signals, SL Profile Code, dan catatan admin tetap admin-only.
- Mendukung review manual, interview, final decision, audit log, dan export.

## Fitur Utama

- Participant assessment flow berbasis access code.
- 76 soal aktif:
  - 45 Community Fit
  - 8 situational judgment
  - 7 honesty/consistency check tersembunyi
  - 16 SL Profile Code items
- Autosave jawaban, resume assessment, dan localStorage fallback saat offline.
- Peserta bisa skip soal dan navigasi lewat nomor soal, tetapi final submit tetap wajib semua soal terjawab.
- Scoring engine untuk Community Fit Score, Competitive Fit Score, Risk Score, Risk Level, Honesty Status, Member Type, dan Final Status.
- SL Profile Code admin-only untuk membaca gaya komunitas secara non-klinis.
- Admin dashboard untuk generate kode, batch code generation, result review, notes, interview history, manual override, lock/unlock/reset code, CSV export, dan audit log.
- Admin Questions tab read-only untuk melihat metadata lengkap question bank.
- Report HTML/Markdown untuk ringkasan hasil peserta.

## Flow Peserta

1. Peserta membuka homepage.
2. Peserta memasukkan access code format `SLFA-XXXX-XXXX`.
3. Peserta membaca instruksi dan memulai assessment.
4. Peserta menjawab 76 soal dengan autosave.
5. Peserta melakukan review jawaban kosong.
6. Peserta submit assessment.
7. Peserta hanya melihat completion screen, tanpa skor atau detail hasil.

Peserta tidak mengisi nama atau Discord data. Nama dan Discord User ID disiapkan oleh admin saat kode dibuat.

## Flow Admin

Admin dapat:

- Login ke dashboard admin.
- Generate single access code dengan nama peserta dan Discord User ID.
- Generate batch code dari CSV textarea.
- Melihat status kode: `Unused`, `In Progress`, `Completed`, `Expired`, atau `Locked`.
- Review hasil assessment, risk reasons, red flags, suspicious flags, category scores, dan SL Profile Code.
- Menambahkan notes dan interview record.
- Override final status dengan alasan wajib.
- Export CSV dan membuka report HTML/Markdown.
- Melihat audit log untuk aksi penting.

## SL Profile Code

SL Profile Code adalah indikator admin-only untuk membaca gaya komunitas peserta, bukan MBTI, diagnosis, atau dasar otomatis final decision.

Axis yang dipakai:

- Social Energy: `S` Social Connector / `Q` Quiet Steady
- Play Drive: `R` Racer Drive / `C` Casual Community
- Rule Style: `A` Admin-Aligned / `N` Needs Rationale
- Conflict Style: `C` Calm Resolver / `E` Expressive Responder

Profile questions dihitung terpisah dari scoring utama, risk score, honesty status, dan final status.

## Tech Stack

- Laravel 13
- Blade web flow
- Tailwind CSS via Vite
- SQLite untuk MVP lokal
- UUID string untuk primary key entity utama

## Local Setup

Install dependency:

```bash
composer install
npm install
```

Siapkan database:

```bash
php artisan migrate --seed
```

Build frontend:

```bash
npm run build
```

Jalankan server lokal:

```bash
php artisan serve
```

Default local admin seed:

```text
admin@squadlimpul.local / password
```

## Verification

```bash
php artisan test
npm run build
```

## Important Routes

Participant:

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

Admin:

```text
GET /admin
GET /admin/questions
GET /admin/batches
GET /admin/scoring-settings
GET /admin/audit-logs
GET /admin/export/results.csv
```

## Project Guardrails

- Jangan expose metadata scoring ke participant UI.
- Jangan tampilkan score, risk detail, red flags, risk reasons, SL Profile Code, atau profile breakdown ke peserta.
- Jangan memakai SL Profile Code sebagai diagnosis/personality klinis atau sebagai dasar otomatis final decision.
- Gunakan `question_number` untuk scoring internal.
- Gunakan `display_order` untuk UI peserta.
- Pertahankan status access code baku: `Unused`, `In Progress`, `Completed`, `Expired`, `Locked`.
- Pertahankan SQLite compatibility untuk MVP.
- Jika menambah kolom, buat migration baru.

## Project Notes

Dokumentasi handoff lengkap ada di `PROJECT_HANDOFF_SUMMARY.md`.
