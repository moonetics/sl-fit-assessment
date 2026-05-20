# Development Phases
# Squad Limpul Community Fit Assessment

Dokumen ini memecah PRD, bank soal, scoring rules, data model, dan implementation checklist menjadi fase kerja yang lebih aman untuk dibangun bertahap. Tujuannya bukan langsung membuat semua fitur, tetapi memastikan setiap fase bisa diuji, dipakai, dan diperbaiki sebelum lanjut ke fase berikutnya.

## Phase 0 - Foundation & Product Shape

**Tujuan:** Membentuk identitas produk dan kerangka dasar aplikasi agar arah build jelas.

**Scope utama:**
- Branding Squad Limpul pada landing page.
- Deskripsi komunitas:
  - Squad Limpul adalah komunitas Roblox chill untuk hang out, race, dan membuat momen bareng.
  - SL berarti Squad Limpul.
  - Race tetap penting, tetapi member casual tetap diterima.
- Footer dinamis: `Managed Squad Limpul` dan tahun berjalan.
- Struktur awal halaman participant entry.
- Penentuan cara menyimpan bank soal dan konfigurasi scoring.

**Output:**
- Landing page awal.
- Roadmap fase di frontend.
- Dokumen fase ini sebagai panduan implementasi.

**Acceptance check:**
- User bisa melihat identitas Squad Limpul dengan jelas.
- Footer memakai tahun berjalan dari server.
- Fase berikutnya sudah punya scope yang jelas.

## Phase 1 - Participant MVP

**Tujuan:** Peserta bisa masuk memakai kode, membaca instruksi, mengerjakan assessment, dan submit final.

**Scope utama:**
- Input access code dengan format `CFA-XXXX-XXXX`.
- Status kode: `Unused`, `In Progress`, `Completed`, `Expired`, `Locked`.
- Halaman instruksi dan disclaimer non-klinis.
- Form data minimal: display name dan Discord username.
- Render 76 soal dari bank soal.
- Progress bar: `Soal X dari 76`.
- Navigasi next/back.
- Submit confirmation dengan peringatan jawaban final tidak bisa diubah.
- Completion screen tanpa skor detail.

**Data minimal:**
- `access_codes`
- `participants`
- `questions`
- `answers`
- `assessment_sessions`

**Acceptance check:**
- Kode valid bisa memulai assessment.
- Kode completed tidak bisa dipakai ulang.
- Semua soal wajib terjawab sebelum final submit.
- Peserta melihat completion screen setelah submit.

## Phase 2 - Autosave, Resume & Session Safety

**Tujuan:** Assessment tetap aman saat refresh, koneksi putus, atau peserta kembali beberapa saat kemudian.

**Scope utama:**
- Autosave setiap jawaban.
- Autosave saat pindah soal.
- Local storage fallback saat offline.
- Resume dari soal terakhir yang belum selesai.
- Session token hashed.
- Device count dasar.
- Refresh count dan resume count.
- Submit final idempotent dengan `submission_attempt_id`.

**Acceptance check:**
- Refresh browser tidak menghapus jawaban.
- Peserta bisa melanjutkan assessment dengan kode `In Progress`.
- Offline tidak langsung menghilangkan jawaban lokal.
- Submit double-click hanya menghasilkan satu final submission.
- Device ke-3 bisa mengunci kode atau masuk review sesuai policy.

## Phase 3 - Scoring Core

**Tujuan:** Sistem bisa menghitung hasil otomatis berdasarkan referensi scoring tanpa melabeli peserta secara klinis.

**Scope utama:**
- Normal scoring.
- Reverse scoring.
- Situational scoring.
- Category score 0-100.
- Community Fit Score.
- Competitive Fit Score.
- Risk Score dan Risk Level.
- Honesty Status.
- Red flag rules.
- Member Type.
- Final Status.

**Output utama:**
- `community_fit_score`
- `competitive_fit_score`
- `risk_level`
- `honesty_status`
- `member_type`
- `final_status`
- `category_scores`
- `red_flags`
- `suspicious_flags`

**Acceptance check:**
- Community Fit tinggi dan risk low bisa menjadi `Accepted`.
- Competitive Fit rendah tidak otomatis ditolak.
- Risk High tidak bisa langsung `Accepted`.
- Honesty Questionable masuk `Manual Review`.
- Honesty Invalid diarahkan ke `Retest` atau `Rejected` sesuai risk.

## Phase 4 - Admin Dashboard MVP

**Tujuan:** Admin bisa mengelola kode, melihat hasil, dan membuat keputusan dengan catatan.

**Scope utama:**
- Login admin.
- Generate access code single.
- List kode dan peserta.
- Filter status kode.
- Detail hasil participant.
- Score cards.
- Category breakdown.
- Red flag viewer.
- Suspicious activity summary.
- Admin notes.
- Manual override final status dengan catatan wajib.
- Audit log untuk aksi penting.

**Acceptance check:**
- Admin bisa membuat kode baru.
- Admin bisa melihat progress dan status peserta.
- Admin bisa melihat hasil scoring.
- Override wajib menyimpan alasan.
- Lock/reset/unlock tercatat di audit log.

## Phase 5 - Export, QA & Hardening

**Tujuan:** Membuat sistem lebih siap dipakai nyata oleh komunitas.

**Scope utama:**
- Export CSV dasar.
- Rate limit input kode.
- Hash access code.
- Hash IP/device data jika dipakai untuk abuse detection.
- QA edge cases dari checklist.
- Empty state, error state, dan loading state.
- Retention policy data peserta.
- Copywriting yang tetap ringan dan non-klinis.

**Acceptance check:**
- Salah kode berulang terkena rate limit.
- Export hanya bisa diakses admin/owner.
- Semua final submit bersifat immutable.
- Hasil assessment tidak dipublikasikan ke peserta/member lain secara default.

## Phase 6 - Future Improvements

Fase ini dikerjakan setelah MVP stabil.

**Candidate scope:**
- Batch generate code.
- Analytics per batch rekrutmen.
- Template pesan Discord.
- Configurable threshold scoring.
- Randomisasi urutan soal.
- Export summary Markdown/PDF.
- Discord OAuth.
- Auto role Discord.
- Interview module untuk Manual Review.

## Recommended Build Order

1. Phase 0 - Foundation & Product Shape.
2. Phase 1 - Participant MVP.
3. Phase 2 - Autosave, Resume & Session Safety.
4. Phase 3 - Scoring Core.
5. Phase 4 - Admin Dashboard MVP.
6. Phase 5 - Export, QA & Hardening.
7. Phase 6 - Future Improvements.

## Notes for Squad Limpul

- Assessment ini harus tetap terasa ringan karena komunitasnya chill.
- Jangan membuat peserta merasa sedang menjalani tes klinis.
- Skill race bukan syarat utama diterima.
- Racer jago tetapi toxic harus tetap bisa masuk review ketat.
- Member casual yang sopan dan stabil harus tetap punya jalur accepted.
