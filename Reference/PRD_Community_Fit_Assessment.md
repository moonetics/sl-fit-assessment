# Product Requirements Document (PRD)
# Community Fit Assessment untuk Komunitas Roblox Obby Berbasis Discord

**Versi:** 1.0  
**Bahasa:** Indonesia  
**Status:** Draft siap review developer  
**Jenis produk:** Platform assessment non-klinis untuk rekrutmen komunitas online  
**Target platform:** Web app responsive + Admin dashboard  
**Catatan penting:** Platform ini bukan psikotes klinis dan tidak boleh dipakai untuk diagnosis psikologis, medis, gangguan mental, trauma, atau kepribadian klinis. Hasil hanya alat bantu keputusan admin.

---

## 1. Product Overview

### Nama Produk
**Community Fit Assessment**

### Deskripsi Singkat
Community Fit Assessment adalah platform assessment berbasis web untuk membantu admin komunitas Roblox obby berbasis Discord menyaring calon member berdasarkan kecocokan perilaku, sportivitas, kepatuhan aturan, risiko toxic/drama, dan kesiapan berkomunitas. Platform ini membedakan calon member kompetitif dan casual tanpa menjadikan skill race sebagai syarat utama penerimaan.

### Latar Belakang
Komunitas Roblox obby memiliki dinamika sosial yang unik: ada unsur kompetitif seperti race, time trial, leaderboard, dan fastest completion, tetapi komunitas tetap membutuhkan ruang aman bagi member casual, supporter, dan member pendiam. Masalah umum komunitas online adalah toxic behavior, drama, provokasi, konflik antar member, pelanggaran aturan, dan calon member yang terlihat “jago” tetapi sulit diarahkan.

### Tujuan Utama
1. Menyaring calon member komunitas online dengan cara konsisten dan terdokumentasi.
2. Mengurangi risiko toxic behavior, konflik, drama, provokasi, dan rule-breaking.
3. Membantu admin membedakan member kompetitif yang sehat, member casual yang aman, dan member berisiko.
4. Menghasilkan rekomendasi keputusan: Accepted, Accepted as Casual Member, Accepted with Trial, Manual Review, Watchlist, Retest, atau Rejected.
5. Menyediakan mekanisme kode unik, autosave, resume, dan one-time final submission.

### Prinsip Produk
| Prinsip | Implementasi |
|---|---|
| Non-klinis | Menggunakan istilah community fit, behavioral tendency, risk indicator, consistency check, dan member suitability. |
| Tidak mengutamakan skill | Peserta tidak boleh ditolak hanya karena bukan racer. |
| Human-in-the-loop | Admin dapat override hasil otomatis dengan catatan manual. |
| Fair untuk member casual | Competitive Fit rendah tidak otomatis buruk. |
| Safety komunitas | Risk Level tinggi tidak boleh langsung Accepted walaupun kompetitif. |
| Transparansi terbatas | Peserta tahu tujuan umum assessment, tetapi tidak tahu item honesty/consistency. |
| Resilient UX | Internet mati, refresh, atau browser tertutup tidak langsung menghapus progress. |

---

## 2. Problem Statement

### Masalah Komunitas Online
Komunitas online mudah terdampak perilaku toxic, provokasi, drama, oversharing konflik, meremehkan member lain, dan persaingan tidak sehat. Satu member problematik dapat mengganggu pengalaman banyak member.

### Masalah Rekrutmen Member
Admin sering hanya mengandalkan chat singkat, rekomendasi, atau skill bermain. Cara ini tidak cukup untuk melihat kecocokan perilaku jangka panjang, terutama pada komunitas yang punya channel Discord aktif.

### Masalah Toxic/Drama/Conflict
Racer yang sangat kompetitif bisa menjadi aset, tetapi jika tidak bisa menerima kalah, menghina member casual, atau melawan admin, maka risiko komunitas meningkat.

### Masalah Internet Mati Saat Tes
Assessment online rentan terganggu koneksi. Tanpa autosave dan resume, peserta bisa kehilangan jawaban, frustrasi, atau mengulang dari awal.

### Masalah Kode Dipakai Ulang
Jika kode bisa dipakai berkali-kali, peserta dapat mengulang tes untuk mencari jawaban “terbaik”, membagikan kode, atau mengubah jawaban setelah melihat hasil.

---

## 3. Goals

### Tujuan Bisnis/Komunitas
- Meningkatkan kualitas member baru.
- Mengurangi beban moderasi setelah member masuk.
- Membuat proses rekrutmen lebih konsisten, adil, dan terdokumentasi.
- Mempertahankan budaya komunitas yang kompetitif tetapi tetap sehat.

### Tujuan User/Peserta
- Bisa mengikuti assessment dengan instruksi jelas.
- Bisa melanjutkan test jika koneksi terputus.
- Tidak merasa harus menjadi racer tercepat untuk diterima.
- Mendapat pengalaman test yang ringan, jelas, dan tidak menyeramkan.

### Tujuan Admin
- Membuat kode unik untuk peserta.
- Melihat progress, hasil, red flag, skor kategori, dan rekomendasi.
- Mengambil keputusan dengan dukungan data.
- Memberikan catatan manual dan override hasil otomatis jika perlu.

### Tujuan Teknis
- Menyediakan one-time final submission.
- Menyediakan autosave per jawaban.
- Menyediakan resume dari progress terakhir.
- Mendeteksi suspicious behavior dasar.
- Menyimpan audit log untuk perubahan penting.

---

## 4. Non-Goals

### Tidak Dibuat di MVP
- Integrasi Discord OAuth.
- Auto-assign role Discord.
- AI interview/chatbot.
- PDF report otomatis.
- Multi-community tenant management.
- Advanced anti-cheat fingerprinting.
- Real-time leaderboard assessment.

### Hal yang Tidak Boleh Dilakukan Platform
- Mendiagnosis gangguan psikologis, penyakit mental, trauma, atau kondisi medis.
- Mengklaim hasil 100% akurat.
- Menolak peserta hanya karena skill race rendah.
- Menggunakan hasil assessment sebagai satu-satunya dasar keputusan mutlak.
- Menampilkan label honesty/consistency kepada peserta.

### Batasan Non-Klinis
Assessment hanya mengukur kecenderungan perilaku dalam konteks komunitas online. Label seperti Risk Level, Drama Risk, dan Toxicity Control adalah indikator operasional komunitas, bukan diagnosis.

---

## 5. Target Users

| User | Kebutuhan Utama |
|---|---|
| Calon member | Mengisi assessment, menyimpan progress, submit final satu kali. |
| Admin komunitas | Generate kode, melihat hasil, memutuskan penerimaan. |
| Moderator | Membantu review red flag dan catatan perilaku. |
| Owner komunitas | Melihat kualitas rekrutmen dan tren risiko komunitas. |

---

## 6. User Personas

### Persona 1 — Racer Kompetitif yang Sehat
- **Nama:** Raka
- **Motivasi:** Ingin ikut race dan time trial.
- **Perilaku:** Bisa menerima kalah, menghargai admin, membantu member baru.
- **Risiko:** Terlalu fokus pada leaderboard, tetapi tetap kooperatif.
- **Expected Result:** Competitive Racer atau Accepted.

### Persona 2 — Member Casual yang Aman
- **Nama:** Naya
- **Motivasi:** Ingin nongkrong di Discord dan sesekali main obby.
- **Perilaku:** Sopan, tidak toxic, tidak mengejar leaderboard.
- **Risiko:** Kurang aktif di event kompetitif.
- **Expected Result:** Casual Community Member atau Accepted as Casual Member.

### Persona 3 — Racer Jago tapi Toxic
- **Nama:** Dex
- **Motivasi:** Ingin jadi yang tercepat dan dikenal.
- **Perilaku:** Meremehkan pemain lambat, mudah menyalahkan admin, suka flame.
- **Risiko:** Konflik dan drama tinggi.
- **Expected Result:** Competitive but Risky, Watchlist, Manual Review, atau Rejected.

### Persona 4 — Member Pendiam tapi Stabil
- **Nama:** Mika
- **Motivasi:** Ingin ikut komunitas tanpa banyak bicara.
- **Perilaku:** Patuh aturan, tidak memprovokasi, jarang ikut debat.
- **Risiko:** Engagement rendah.
- **Expected Result:** Quiet but Safe atau Accepted as Casual Member.

### Persona 5 — Member Rawan Drama
- **Nama:** Ardi
- **Motivasi:** Ingin punya circle dan perhatian.
- **Perilaku:** Mudah tersinggung, membawa masalah pribadi ke server, suka memilih kubu.
- **Risiko:** Drama Risk tinggi.
- **Expected Result:** Drama-Prone Member, Manual Review, Watchlist, atau Rejected.

### Persona 6 — Admin Komunitas
- **Nama:** Sera
- **Motivasi:** Menjaga server tetap sehat.
- **Tugas:** Generate kode, review hasil, memberi catatan, membuat keputusan final.
- **Pain Point:** Sulit membedakan member kompetitif sehat vs member jago tapi toxic.
- **Expected Need:** Dashboard ringkas, red flag jelas, audit log, dan override.

---

## 7. User Flow

### Flow A — Admin Membuat Kode
1. Admin login.
2. Admin membuka menu **Access Codes**.
3. Admin klik **Generate Code**.
4. Admin mengisi opsional: nama calon member, Discord username, expired date, batch/source.
5. Sistem membuat kode unik dengan status `Unused`.
6. Admin membagikan kode ke calon member.

### Flow B — Peserta Memasukkan Kode
1. Peserta membuka landing page.
2. Peserta memasukkan kode.
3. Sistem validasi:
   - Jika valid dan `Unused`, masuk ke instruksi.
   - Jika `In Progress`, tampilkan resume screen.
   - Jika `Completed`, tampilkan pesan tidak bisa digunakan ulang.
   - Jika `Expired`, tampilkan pesan kode expired.
   - Jika `Locked`, tampilkan pesan hubungi admin.
4. Sistem membuat atau memperbarui session.

### Flow C — Peserta Mengerjakan Assessment
1. Peserta membaca instruksi dan disclaimer non-klinis.
2. Peserta mengisi data minimal: display name dan Discord username.
3. Peserta mulai soal.
4. Sistem menampilkan 1 soal per halaman atau beberapa soal per halaman sesuai konfigurasi.
5. Peserta memilih jawaban.
6. Sistem autosave jawaban.
7. Progress bar diperbarui.

### Flow D — Autosave Berjalan
1. Autosave terjadi setelah peserta memilih jawaban.
2. Autosave juga terjadi saat pindah soal, refresh, atau setiap 10–15 detik jika ada perubahan.
3. UI menampilkan status:
   - “Tersimpan”
   - “Menyimpan…”
   - “Koneksi bermasalah, akan dicoba lagi”
4. Jika autosave gagal, jawaban disimpan sementara di local storage dan dikirim ulang saat online.

### Flow E — Internet Mati
1. Peserta kehilangan koneksi.
2. UI menampilkan error koneksi.
3. Peserta tetap bisa melihat jawaban terakhir yang tersimpan lokal.
4. Submit final diblokir sampai koneksi pulih.
5. Saat koneksi pulih, sistem sync jawaban.

### Flow F — Resume Test
1. Peserta membuka ulang link dan memasukkan kode.
2. Sistem mengecek kode `In Progress`.
3. Sistem mengecek device/session count.
4. Jika device masih dalam batas, peserta diarahkan ke soal terakhir belum lengkap.
5. Jika device lebih dari 2, kode masuk `Locked` dan admin perlu review.

### Flow G — Submit Final
1. Peserta klik **Submit Final**.
2. Sistem memvalidasi semua soal wajib terjawab.
3. Jika lengkap, sistem mengunci jawaban.
4. Status kode berubah menjadi `Completed`.
5. Sistem menghitung skor, red flag, Risk Level, Honesty Status, Member Type, dan Final Status.
6. Peserta melihat completion screen tanpa skor detail, kecuali admin mengaktifkan setting publik.

### Flow H — Admin Melihat Hasil
1. Admin membuka dashboard.
2. Admin memilih peserta/kode.
3. Admin melihat summary, skor kategori, red flag, suspicious flag, durasi, refresh count, device count, dan jawaban.
4. Admin menambahkan notes.
5. Admin menerima, menolak, trial, atau manual review.

---

## 8. Assessment Design

### Struktur Assessment
| Bagian | Jumlah | Format | Tujuan |
|---|---:|---|---|
| Community Fit Scale | 45 | Skala 1–4 | Mengukur perilaku komunitas, sportivitas, aturan, konflik, komitmen. |
| Situational Judgment | 8 | Pilihan ganda | Mengukur respons dalam skenario Discord/race/admin/member casual. |
| Honesty & Consistency | 7 | Skala 1–4 tersisip tersembunyi | Mengecek konsistensi pola jawaban dan klaim terlalu sempurna. |
| SL Profile Code | 16 | Skala 1–4 | Membaca gaya komunitas admin-only secara non-klinis. |
| **Total** | **76** | Campuran | Assessment lengkap non-klinis. |

### Skala 1–4
1. Sangat tidak setuju  
2. Tidak setuju  
3. Setuju  
4. Sangat setuju  

Tidak ada pilihan netral agar peserta memilih kecenderungan yang lebih jelas.

### Normal Scoring
Untuk item positif, skor mengikuti jawaban:
- 1 = 1
- 2 = 2
- 3 = 3
- 4 = 4

Contoh item normal: “Saya bisa menerima kekalahan tanpa menyalahkan pemain lain.”

### Reverse Scoring
Untuk item negatif, skor dibalik:
- 1 = 4
- 2 = 3
- 3 = 2
- 4 = 1

Contoh item reverse: “Kalau saya kalah karena orang lain, wajar kalau saya marah di chat.”

### Honesty & Consistency Check
- Item honesty tidak ditampilkan sebagai bagian khusus.
- Sistem membandingkan pasangan item yang maknanya mirip/berlawanan.
- Sistem menandai klaim terlalu sempurna, jawaban seragam ekstrem, dan kontradiksi.
- Output bukan “bohong”, melainkan `Honesty Status`: Valid, Questionable, atau Invalid.

### Alasan Non-Klinis
Komunitas membutuhkan indikator perilaku praktis, bukan diagnosis. Assessment ini hanya membantu memprediksi kecocokan budaya komunitas, risiko moderasi, dan kebutuhan review manual.

### Peran Behavioral Consultant
- Menjaga bahasa item tetap non-klinis.
- Mengurangi bias item terhadap member casual/non-racer.
- Memastikan indikator risiko tidak menyimpulkan kondisi mental.
- Mendesain consistency check yang wajar dan tidak manipulatif.

---

## 9. Scoring System

### Definisi Kategori
| Kategori | Arah Skor Tinggi |
|---|---|
| Online Behavior | Sopan, tidak spam, menjaga etika Discord. |
| Toxicity Control | Tidak flame, tidak menghina, tidak provokatif. |
| Sportsmanship | Bisa menang/kalah dengan sehat. |
| Competitive Attitude | Suka improve dan race secara sehat. |
| Respect for Casual Members | Menghargai non-racer dan member santai. |
| Conflict Handling | Menyelesaikan konflik tanpa drama. |
| Rule Acceptance | Menerima aturan/admin/moderasi. |
| Accountability | Mau mengakui kesalahan. |
| Drama Risk | Skor tinggi berarti risiko rendah setelah reverse scoring. |
| Community Commitment | Niat ikut komunitas dengan sehat. |
| Honesty & Consistency | Validitas pola jawaban. |

### Bobot Community Fit Score
| Kategori | Bobot |
|---|---:|
| Online Behavior | 12% |
| Toxicity Control | 14% |
| Sportsmanship | 10% |
| Respect for Casual Members | 10% |
| Conflict Handling | 12% |
| Rule Acceptance | 12% |
| Accountability | 10% |
| Drama Risk | 12% |
| Community Commitment | 8% |
| **Total** | **100%** |

### Bobot Competitive Fit Score
| Kategori | Bobot |
|---|---:|
| Competitive Attitude | 45% |
| Sportsmanship | 25% |
| Accountability | 10% |
| Rule Acceptance | 10% |
| Respect for Casual Members | 10% |
| **Total** | **100%** |

### Konversi Skor Kategori ke 0–100
Untuk kategori skala Likert:
```text
category_score_0_100 = ((raw_score - min_possible) / (max_possible - min_possible)) * 100
```

Untuk item situasional:
- Jawaban ideal = 4 poin
- Jawaban acceptable = 3 poin
- Jawaban risky = 2 poin
- Jawaban red flag = 0–1 poin

### Community Fit Score
```text
Community Fit Score =
Σ(category_score_0_100 × category_weight)
```

### Competitive Fit Score
```text
Competitive Fit Score =
Σ(competitive_category_score_0_100 × competitive_weight)
```

### Risk Level
| Kondisi | Risk Level |
|---|---|
| Risk score < 20 dan red flag berat = 0 | Very Low |
| Risk score 20–34 dan red flag berat = 0 | Low |
| Risk score 35–64 atau red flag sedang ≥ 2 | Medium |
| Risk score 65–79 atau red flag berat = 1 | High |
| Risk score ≥ 80 atau red flag berat ≥ 2 | Critical |

Risk score dihitung dari:
```text
risk_score = 
(100 - ToxicityControlScore) × 0.25 +
(100 - ConflictHandlingScore) × 0.20 +
(100 - RuleAcceptanceScore) × 0.20 +
(100 - AccountabilityScore) × 0.15 +
(100 - DramaRiskScore) × 0.20
```

### Honesty Status
| Kondisi | Status |
|---|---|
| Contradiction count 0–1 dan suspicious pattern rendah | Valid |
| Contradiction count 2–3 atau terlalu banyak jawaban ekstrem | Questionable |
| Contradiction count ≥ 4, pola asal-asalan kuat, atau impossible perfection | Invalid |

### Red Flag Rules
Red flag berat minimal membuat status menjadi Watchlist atau lebih ketat:
- Menyatakan admin tidak perlu dihormati jika “salah”.
- Menganggap flame/hinaan wajar dalam kompetisi.
- Menolak aturan dasar komunitas.
- Suka membawa konflik pribadi ke publik.
- Meremehkan member casual secara eksplisit.
- Konsistensi jawaban invalid.

### Manual Review Rules
Peserta masuk Manual Review jika:
- Honesty Status = Questionable.
- Risk Level = Medium dengan skor community fit borderline.
- Competitive Fit tinggi tetapi Risk Level Medium/High.
- Device count > 2.
- Refresh count sangat tinggi.
- Durasi terlalu cepat.
- Jawaban pola sama terlalu banyak.
- Admin/moderator menambahkan flag manual.

### Status Penerimaan
| Kondisi | Final Status |
|---|---|
| Community Fit ≥ 80, Risk Very Low/Low, Honesty Valid, red flag berat 0 | Accepted |
| Community Fit ≥ 70, Competitive Fit < 50, Risk Low/Medium rendah | Accepted as Casual Member |
| Community Fit 65–79, Risk Low/Medium, Honesty Valid | Accepted with Trial |
| Honesty Questionable atau suspicious sedang | Manual Review |
| Red flag berat 1 atau Risk High/Critical tetapi tidak ekstrem | Watchlist |
| Honesty Invalid dan risiko tidak berat | Retest |
| Risk Critical + red flag berat ≥ 2 atau rule resistance tinggi | Rejected |

### Member Type Logic
| Member Type | Logic |
|---|---|
| Competitive Racer | Competitive Fit ≥ 75, Community Fit ≥ 75, Risk Very Low/Low. |
| Casual Community Member | Community Fit ≥ 70, Competitive Fit < 55, Risk Low/Medium rendah. |
| Supportive Member | Respect + Online Behavior + Commitment tinggi, Competitive sedang/rendah. |
| Quiet but Safe | Online Behavior tinggi, Drama Risk rendah, Commitment sedang, Competitive rendah. |
| Competitive but Risky | Competitive ≥ 75, Risk Medium/High/Critical. |
| Drama-Prone Member | Drama Risk rendah atau conflict flags tinggi. |
| Rule-Resistant Member | Rule Acceptance rendah atau admin-resistance flag. |
| Not Recommended | Risk High/Critical + Honesty Invalid/red flags berat. |

---

## 10. Result Summary Design

### Format Summary
```markdown
## Assessment Result Summary

- Nama Peserta:
- Discord Username:
- Kode Peserta:
- Tanggal Submit:
- Community Fit Score:
- Competitive Fit Score:
- Risk Level:
- Honesty Status:
- Member Type:
- Final Status:

### Strengths
-

### Risks
-

### Red Flags
-

### Recommendation
-

### Admin Notes
-
```

### Contoh Summary Hasil Peserta
```markdown
## Assessment Result Summary

- Nama Peserta: RakaObby
- Discord Username: @raka_speed
- Kode Peserta: CFA-7KQ2-M9XA
- Tanggal Submit: 2026-05-18 19:42
- Community Fit Score: 84
- Competitive Fit Score: 79
- Risk Level: Low
- Honesty Status: Valid
- Member Type: Competitive Racer
- Final Status: Accepted

### Strengths
- Menunjukkan sportivitas tinggi saat kalah/menang.
- Menghargai member casual dan tidak meremehkan pemain baru.
- Menerima aturan admin dan siap mengikuti event secara sehat.

### Risks
- Ambisi kompetitif tinggi, perlu diarahkan agar tetap positif.

### Red Flags
- Tidak ada red flag berat.

### Recommendation
Terima sebagai member kompetitif. Cocok diarahkan ke race/time trial, tetapi tetap beri onboarding aturan kompetisi.

### Admin Notes
Admin dapat memantau selama 2 minggu pertama untuk memastikan perilaku race tetap sehat.
```

---

## 11. Code System Requirements

### Format Kode Unik
Format rekomendasi:
```text
CFA-XXXX-XXXX
```
Contoh:
```text
CFA-7KQ2-M9XA
```

Aturan:
- 8 karakter acak alfanumerik uppercase.
- Tidak menggunakan karakter ambigu seperti O/0 dan I/1.
- Disimpan dalam bentuk hashed token untuk keamanan, dengan display code terpisah bila diperlukan.

### Status Kode
| Status | Deskripsi |
|---|---|
| Unused | Kode dibuat tetapi belum digunakan. |
| In Progress | Peserta sudah mulai assessment. |
| Completed | Peserta sudah submit final. Tidak bisa dipakai ulang. |
| Expired | Kode melewati expired date. |
| Locked | Kode dikunci karena suspicious activity atau tindakan admin. |

### One-Time Final Submission
- Final submit hanya boleh sekali.
- Setelah submit, jawaban immutable.
- Attempt submit kedua harus ditolak dengan pesan “Assessment sudah selesai.”

### Expiration Logic
- Kode memiliki `expires_at`.
- Jika belum mulai dan expired, tidak bisa dipakai.
- Jika sedang mengerjakan saat expired, konfigurasi MVP:
  - default: boleh lanjut sampai submit selama session masih aktif,
  - admin bisa mengubah ke strict expiration jika diperlukan.

### Locked Logic
Kode berubah `Locked` jika:
- Device/browser lebih dari 2.
- Terjadi terlalu banyak validasi kode gagal.
- Sistem mendeteksi suspicious activity berat.
- Admin mengunci manual.

### Reset Logic
Admin dapat:
- Reset ke `Unused` jika belum completed dan alasan valid.
- Reset progress jawaban dengan konfirmasi dua langkah.
- Membuat kode baru jika kode lama bermasalah.
- Semua reset masuk audit log.

### Admin Override
Admin dapat mengubah Final Status dengan wajib mengisi catatan:
- alasan override,
- admin yang melakukan,
- waktu perubahan,
- status sebelum/sesudah.

---

## 12. Autosave & Resume Requirements

### Kapan Autosave Terjadi
- Setelah peserta memilih jawaban.
- Saat peserta klik Next/Back.
- Setiap 10–15 detik jika ada perubahan.
- Saat browser mendeteksi `visibilitychange` atau tab akan ditutup.
- Saat koneksi pulih setelah offline.

### Data yang Disimpan
- Participant ID.
- Code ID.
- Question ID.
- Answer value.
- Timestamp.
- Current question index.
- Session ID.
- Device fingerprint ringan.
- Autosave status.
- Version/revision number untuk mencegah overwrite.

### Cara Resume
1. Peserta memasukkan kode.
2. Sistem memuat jawaban terakhir.
3. Sistem menampilkan resume screen.
4. Peserta klik “Lanjutkan”.
5. Sistem membuka soal terakhir yang belum dijawab.

### Konflik Device
- Device 1 dan 2 diizinkan.
- Device ke-3 membuat kode `Locked`.
- Jika dua device aktif bersamaan, sistem hanya mengizinkan session terbaru untuk menulis jawaban.
- Session lama menjadi read-only atau diminta refresh.

### Internet Mati
- Jawaban disimpan ke local storage.
- UI memberi pesan offline.
- Submit final disabled.
- Saat online, sistem sync jawaban yang belum tersimpan.

### Browser Tertutup
- Progress terakhir tetap tersimpan dari autosave.
- Saat kembali, peserta dapat resume dari soal terakhir.

### Submit Gagal
- Sistem tidak mengubah status ke Completed sebelum transaksi submit dan scoring berhasil.
- Jika submit timeout, peserta melihat status “Submit belum terkonfirmasi, coba cek ulang.”
- Backend harus idempotent memakai `submission_attempt_id`.

### Data Belum Lengkap
- Submit final diblokir.
- Sistem menampilkan daftar soal yang belum dijawab.
- Peserta diarahkan ke soal pertama yang kosong.

---

## 13. Admin Dashboard Requirements

### Fitur Dashboard
| Fitur | Requirement |
|---|---|
| Login admin | Email/password atau magic link untuk MVP. |
| Generate kode | Single generate dan batch generate. |
| List peserta/kode | Tabel dengan search, filter status, filter risk. |
| Status pengerjaan | Unused, In Progress, Completed, Expired, Locked. |
| Detail hasil peserta | Summary skor, kategori, flags, jawaban. |
| Red flag viewer | Menampilkan jenis flag, severity, evidence item. |
| Manual review panel | Admin dapat memilih keputusan final. |
| Notes admin | Catatan internal dengan timestamp. |
| Export data | CSV untuk participant, result, code status. |
| Reset/lock/unlock | Aksi admin dengan konfirmasi. |
| Audit log | Melacak generate, reset, override, lock/unlock, export. |

### Kolom List Peserta
- Code
- Participant name
- Discord username
- Status kode
- Progress %
- Community Fit
- Competitive Fit
- Risk Level
- Honesty Status
- Final Status
- Red flag count
- Last activity
- Action

### Detail Hasil Peserta
- Header summary.
- Score cards.
- Category breakdown.
- Red flag section.
- Suspicious activity section.
- Answer review table.
- Admin notes.
- Decision controls.

---

## 14. Participant Interface Requirements

### Halaman Input Kode
- Field kode.
- Tombol lanjut.
- Error untuk kode salah, expired, locked, completed.
- Rate limit jika terlalu banyak percobaan.

### Halaman Instruksi Assessment
Harus menjelaskan:
- Assessment bukan psikotes klinis.
- Tidak ada jawaban sempurna.
- Jawab sesuai kebiasaan nyata.
- Skill obby bukan syarat utama.
- Jawaban final hanya bisa submit satu kali.
- Progress tersimpan otomatis.

### Halaman Soal
- Teks soal.
- Pilihan jawaban.
- Tombol Next/Back.
- Progress bar.
- Autosave indicator.
- Nomor soal.
- Tidak menampilkan kategori atau label honesty.

### Progress Bar
- Menampilkan “Soal X dari 76”.
- Menampilkan persentase progress.

### Autosave Indicator
- “Menyimpan…”
- “Tersimpan”
- “Offline — jawaban akan disimpan saat koneksi kembali”
- “Gagal menyimpan — coba lagi”

### Error Koneksi
- Banner jelas di atas/bawah layar.
- Submit disabled saat offline.
- Jawaban lokal tetap dipertahankan.

### Resume Screen
- “Kamu sudah memulai assessment ini.”
- Last saved timestamp.
- Tombol “Lanjutkan dari soal terakhir”.
- Pesan jika kode locked/completed.

### Submit Confirmation
- Menampilkan ringkasan jumlah soal terjawab.
- Checkbox konfirmasi: “Saya memahami jawaban final tidak bisa diubah.”
- Tombol Submit Final.

### Completion Screen
- “Assessment berhasil dikirim.”
- “Admin akan meninjau hasil.”
- Tidak menampilkan skor detail secara default.

---

## 15. Data Requirements

| Data | Field Minimal |
|---|---|
| Participant data | id, display_name, discord_username, created_at |
| Code data | id, code_hash, display_code, status, expires_at, created_by |
| Answer data | participant_id, question_id, answer_value, saved_at, revision |
| Score data | community_fit, competitive_fit, risk_level, honesty_status, member_type, final_status |
| Session data | session_id, participant_id, started_at, last_seen_at, refresh_count, resume_count |
| Device data | device_id, user_agent, ip_hash, first_seen_at, last_seen_at |
| Admin notes | participant_id, admin_id, note, created_at |
| Audit logs | actor_id, action, entity_type, entity_id, before, after, created_at |

---

## 16. Database Entity Suggestions

### `admins`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| email | varchar | unique |
| password_hash | varchar | nullable jika magic link |
| role | enum | owner/admin/moderator |
| created_at | timestamp |  |

### `access_codes`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| code_hash | varchar | unique |
| display_code | varchar | optional encrypted/masked |
| status | enum | Unused/In Progress/Completed/Expired/Locked |
| expires_at | timestamp | nullable |
| created_by | uuid | FK admins |
| completed_at | timestamp | nullable |
| locked_reason | text | nullable |

### `participants`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| access_code_id | uuid | FK |
| display_name | varchar |  |
| discord_username | varchar |  |
| created_at | timestamp |  |

### `questions`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| question_number | int | 1–76 |
| text | text |  |
| question_type | enum | likert/situational |
| category | varchar | hidden from participant |
| scoring_direction | enum | normal/reverse/situational |
| is_consistency_item | boolean | hidden |
| is_active | boolean |  |

### `answers`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| participant_id | uuid | FK |
| question_id | uuid | FK |
| answer_value | varchar/int |  |
| score_value | int | computed |
| saved_at | timestamp |  |
| revision | int | optimistic lock |

### `assessment_sessions`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| participant_id | uuid | FK |
| session_token_hash | varchar |  |
| device_id | varchar |  |
| started_at | timestamp |  |
| last_seen_at | timestamp |  |
| refresh_count | int | default 0 |
| resume_count | int | default 0 |

### `results`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| participant_id | uuid | FK |
| community_fit_score | int | 0–100 |
| competitive_fit_score | int | 0–100 |
| risk_level | enum | Very Low/Low/Medium/High/Critical |
| honesty_status | enum | Valid/Questionable/Invalid |
| member_type | enum |  |
| final_status | enum |  |
| red_flags | jsonb |  |
| category_scores | jsonb |  |
| generated_at | timestamp |  |

### `admin_notes`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| participant_id | uuid | FK |
| admin_id | uuid | FK |
| note | text |  |
| created_at | timestamp |  |

### `audit_logs`
| Field | Type | Notes |
|---|---|---|
| id | uuid | PK |
| actor_id | uuid | FK admins |
| action | varchar | e.g. CODE_RESET |
| entity_type | varchar |  |
| entity_id | uuid |  |
| before_data | jsonb | nullable |
| after_data | jsonb | nullable |
| created_at | timestamp |  |

---

## 17. Edge Cases

| Edge Case | Solusi |
|---|---|
| Internet mati | Simpan lokal, tampilkan offline banner, sync saat online. |
| Peserta refresh halaman | Resume dari saved progress; increment refresh_count. |
| Peserta ganti device | Izinkan maksimal 2 device; device ke-3 locked. |
| Peserta submit dua kali | Backend idempotent; status Completed menolak submit kedua. |
| Kode expired saat mengerjakan | Default MVP: boleh lanjut jika session sudah In Progress; tandai admin warning. |
| Kode salah | Tampilkan error generik; rate limit. |
| Kode sudah completed | Tolak akses dan tampilkan pesan final submitted. |
| Jawaban belum lengkap | Submit disabled; tampilkan soal kosong. |
| Autosave gagal | Retry exponential backoff; simpan local backup. |
| Peserta terlalu cepat selesai | Flag suspicious speed dan Manual Review. |
| Jawaban pola semua sama | Flag straight-lining. |
| Admin salah reset kode | Audit log + soft restore jika belum ada submit baru. |

---

## 18. Security & Privacy

### Proteksi Kode
- Kode sulit ditebak.
- Code validation rate-limited.
- Kode disimpan hashed.
- Pesan error tidak membocorkan detail berlebihan.

### Rate Limiting Input Kode
- Maksimal 5 percobaan per 10 menit per IP/device.
- Setelah limit, cooldown.
- Percobaan gagal dicatat.

### Validasi Session
- Session token random, disimpan hashed.
- CSRF protection untuk web form.
- Session timeout konfigurabel.
- Session terbaru menjadi writer utama.

### Akses Admin
- Role-based access.
- Owner dapat export dan override.
- Moderator hanya review dan notes jika dikonfigurasi.
- Semua aksi sensitif masuk audit log.

### Penyimpanan Data Peserta
- Simpan data minimal.
- Hindari data pribadi tidak perlu.
- IP disimpan sebagai hash jika hanya untuk deteksi abuse.
- Retention policy: misalnya hapus data mentah setelah 6–12 bulan.

### Disclaimer Non-Klinis
Ditampilkan di instruksi peserta dan dashboard admin:
> Assessment ini bukan psikotes klinis dan tidak mendiagnosis kondisi psikologis/medis. Hasil hanya indikator kecocokan komunitas dan alat bantu admin.

### Batasan Penggunaan Hasil
- Hasil tidak boleh dipublikasikan ke member lain.
- Hasil tidak boleh dipakai untuk mempermalukan peserta.
- Admin harus mempertimbangkan konteks dan dapat melakukan interview/manual review.

---

## 19. MVP Scope

### Must-Have
- Input kode.
- Status kode: Unused, In Progress, Completed, Expired, Locked.
- Assessment 76 soal.
- Autosave per jawaban.
- Resume dari progress terakhir.
- Submit final satu kali.
- Scoring otomatis.
- Risk Level, Honesty Status, Member Type, Final Status.
- Admin login.
- Generate kode.
- List peserta/kode.
- Detail hasil peserta.
- Admin notes.
- Manual override dengan audit log.
- Export CSV dasar.

### Should-Have
- Batch generate kode.
- Filter dashboard lengkap.
- Red flag viewer detail.
- Device count detection.
- Refresh/resume counter.
- Local storage offline buffer.

### Could-Have
- Template pesan Discord untuk undangan.
- Basic analytics per batch.
- Configurable threshold scoring.
- Export result summary Markdown.

### Won’t-Have for Now
- Discord OAuth.
- Auto role Discord.
- AI scoring.
- PDF generator.
- Multi-community SaaS.
- Full fraud detection engine.

---

## 20. Acceptance Criteria

### Input Kode
- Given kode valid Unused, when peserta submit kode, then sistem membuka instruksi.
- Given kode Completed, when peserta submit kode, then sistem menolak akses.
- Given kode salah 5 kali, then rate limit aktif.

### Start Assessment
- Given peserta menyetujui instruksi, when klik Start, then status kode menjadi In Progress.
- Sistem membuat participant dan session.

### Autosave
- Given peserta menjawab soal, then jawaban tersimpan tanpa klik submit.
- Given koneksi offline, then UI menampilkan status offline dan menyimpan local backup.

### Resume
- Given assessment In Progress, when peserta masuk ulang dengan kode sama, then sistem membuka resume screen.
- Given device count > 2, then kode menjadi Locked.

### Submit Final
- Given semua 76 soal terjawab, when submit final, then status kode menjadi Completed.
- Given ada soal kosong, then submit ditolak dan peserta diarahkan ke soal kosong.
- Given submit dikirim dua kali, then backend hanya memproses satu kali.

### Scoring
- Sistem menghitung Community Fit 0–100.
- Sistem menghitung Competitive Fit 0–100.
- Sistem menentukan Risk Level, Honesty Status, Member Type, dan Final Status.
- Red flag berat mengubah status minimal Watchlist.

### Dashboard Admin
- Admin dapat melihat list kode dan filter status.
- Admin dapat membuka detail hasil peserta.
- Admin dapat menambah notes.
- Admin dapat export CSV.

### Manual Review
- Honesty Questionable otomatis Manual Review.
- Admin dapat override Final Status dengan catatan wajib.

### Code Status
- Status berubah sesuai lifecycle.
- Reset/lock/unlock tercatat di audit log.

---

## 21. Future Improvements

- Integrasi Discord OAuth untuk verifikasi akun.
- Auto role Discord setelah accepted.
- Notifikasi Discord/email untuk admin.
- Advanced analytics per batch rekrutmen.
- Randomisasi question bank.
- A/B testing pertanyaan.
- Improved fraud detection.
- Export CSV/PDF lebih lengkap.
- Multi-community support.
- Public applicant portal dengan status aplikasi.
- Interview module untuk Manual Review.
- Configurable scoring profile per komunitas.

---

## 22. Risks & Mitigations

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Peserta merasa tes terlalu serius | Drop-off | Gunakan bahasa ringan, tekankan bukan psikotes klinis. |
| Salah interpretasi hasil | Keputusan tidak adil | Dashboard menampilkan disclaimer dan rekomendasi, bukan vonis mutlak. |
| False positive red flag | Peserta aman ditandai berisiko | Manual Review dan admin override. |
| Data privacy issue | Kepercayaan turun | Minimasi data, akses terbatas, retention policy. |
| Admin terlalu bergantung pada skor | Human judgment hilang | Wajib catatan untuk keputusan sensitif; tampilkan “alat bantu”. |
| Assessment dibocorkan | Jawaban dapat dimanipulasi | Randomisasi soal di future; consistency checks; retest. |
| Peserta asal jawab | Hasil tidak valid | Straight-lining detection, speed flag, Honesty Status. |
| Kode dibagikan | Orang lain ikut tes | Device/session tracking, kode one-time, Discord username. |
| Skill bias terhadap non-racer | Member casual dirugikan | Competitive Fit tidak menjadi syarat utama. |

---

## 23. Open Questions

1. Apakah peserta boleh melihat hasil ringkas atau hanya completion message?
2. Berapa durasi minimum yang dianggap “terlalu cepat” untuk 76 soal?
3. Berapa masa berlaku default kode? 24 jam, 3 hari, atau 7 hari?
4. Apakah expired saat In Progress tetap boleh lanjut sampai selesai?
5. Apakah admin membutuhkan batch kode per event rekrutmen?
6. Apakah Discord username wajib diverifikasi manual?
7. Apakah owner dan moderator memiliki permission berbeda?
8. Apakah ada batas usia/minor policy untuk komunitas?
9. Apakah jawaban mentah boleh diekspor, atau hanya summary?
10. Berapa lama data peserta disimpan?
11. Apakah Final Status otomatis boleh langsung Accepted, atau semua tetap perlu approval admin?
12. Apakah komunitas ingin threshold yang bisa dikonfigurasi?
13. Apakah assessment tersedia dalam bahasa lain?
14. Apakah perlu captcha pada input kode?
15. Apakah peserta yang Retest mendapat kode baru atau reset kode lama?
16. Apakah sistem perlu menampilkan alasan penolakan ke peserta?
17. Siapa yang boleh melihat red flag detail?
18. Apakah question order perlu fixed atau randomized pada MVP?

---

## Appendix A — Developer Notes

### Recommended Stack MVP
- Frontend: Next.js/React.
- Backend: Node.js/NestJS atau Laravel.
- Database: PostgreSQL.
- Auth admin: NextAuth/Auth.js, Clerk, atau custom email/password.
- Hosting: Vercel + managed PostgreSQL, atau VPS.
- Export: CSV server-side.

### Important Backend Behavior
- Semua final submit harus transactional.
- `Completed` harus immutable untuk participant answers.
- `answers` memakai unique key `(participant_id, question_id)`.
- Autosave memakai upsert.
- Scoring dapat dijalankan ulang admin hanya jika jawaban belum berubah atau berdasarkan snapshot.

---

> **💡 Pro Tip:** Are you ready to create a slide deck from this PRD? Don't start from scratch. Use **Gamma** to convert this PRD into a presentation automatically.
Use the [Gamma AI Presentation Generator](https://try.gamma.app/PRD)
_(Sponsored)_
✅ Professional slides, auto-formatted  
✅ Dozens of polished, customizable templates  
✅ Export and share easily (PDF/PowerPoint)  
✅ No credit card required  

👉 **[Create with Gamma AI – For Free](https://try.gamma.app/PRD)**
