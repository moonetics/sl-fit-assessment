# Implementation Checklist
# Community Fit Assessment

## MVP Build Checklist

### Participant App
- [ ] Landing page/input kode.
- [ ] Kode validation dengan rate limit.
- [ ] Instruksi assessment + disclaimer non-klinis.
- [ ] Start assessment.
- [ ] Render 76 soal.
- [ ] Progress bar.
- [ ] Autosave setelah jawaban dipilih.
- [ ] Local storage fallback saat offline.
- [ ] Resume dari soal terakhir.
- [ ] Submit confirmation.
- [ ] Final submit idempotent.
- [ ] Completion screen.

### Admin Dashboard
- [ ] Login admin.
- [ ] Generate kode single.
- [ ] List kode dengan filter.
- [ ] Detail peserta.
- [ ] Category score display.
- [ ] Risk/Honesty/Member Type display.
- [ ] Red flag viewer.
- [ ] Admin notes.
- [ ] Manual override final status.
- [ ] Reset kode.
- [ ] Lock/unlock kode.
- [ ] Export CSV.
- [ ] Audit log.

### Scoring Engine
- [ ] Normal scoring.
- [ ] Reverse scoring.
- [ ] Situational scoring.
- [ ] Category score.
- [ ] Community Fit Score.
- [ ] Competitive Fit Score.
- [ ] Risk Score/Level.
- [ ] Honesty Status.
- [ ] Member Type.
- [ ] Final Status.
- [ ] Red flag precedence.

### Security & Privacy
- [ ] Kode random kuat.
- [ ] Kode disimpan hashed.
- [ ] Rate limit input kode.
- [ ] Session token aman.
- [ ] Admin RBAC.
- [ ] Audit log aksi sensitif.
- [ ] Minimal data pribadi.
- [ ] Disclaimer non-klinis.
- [ ] Export hanya admin/owner.

### QA Cases
- [ ] Kode Unused dapat mulai.
- [ ] Kode In Progress dapat resume.
- [ ] Kode Completed ditolak.
- [ ] Kode Expired ditolak sebelum start.
- [ ] Kode Locked ditolak.
- [ ] Submit dengan soal kosong gagal.
- [ ] Submit final dua kali hanya memproses sekali.
- [ ] Offline saat isi soal tidak menghapus jawaban.
- [ ] Device ke-3 mengunci kode.
- [ ] Honesty Questionable masuk Manual Review.
- [ ] High Risk tidak langsung Accepted.
- [ ] Community Fit tinggi + Competitive rendah bisa Accepted as Casual Member.
