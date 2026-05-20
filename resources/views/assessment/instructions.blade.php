<x-layouts.participant title="Welcome to Squad Limpul Assessment">
    <section class="rounded-lg border border-[#d7cfbf] bg-white p-6 shadow-[0_20px_70px_rgba(38,31,15,0.08)] sm:p-8">
        @php($welcomeName = $accessCode->assigned_name ?: $participant?->display_name)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">WELCOME TO SQUAD LIMPUL FIT ASSESSMENT</p>
                <h1 class="mt-3 text-3xl font-black text-[#191919]">
                    {{ $welcomeName ? 'Hai, '.$welcomeName : 'Selamat datang di assessment' }}
                </h1>
            </div>
            <span class="rounded-md bg-[#f9d65c] px-3 py-1 font-mono text-xs font-black text-[#191919]">{{ $accessCode->display_code }}</span>
        </div>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-[#5d5850]">
            Sebelum mulai, baca ringkasan singkat ini dulu. Assessment ini membantu admin Squad Limpul memahami kecocokan komunitas secara ringan dan non-klinis. Tidak ada jawaban sempurna; jawab sesuai kebiasaan kamu di komunitas online.
        </p>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <h2 class="font-black text-[#191919]">Tujuan</h2>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-[#514c45]">
                    <li>Menilai kecocokan dengan budaya Squad Limpul.</li>
                    <li>Membantu admin mengenal cara kamu berkomunitas.</li>
                    <li>Skill obby atau race bukan satu-satunya hal yang dinilai.</li>
                </ul>
            </div>
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <h2 class="font-black text-[#191919]">Aturan singkat</h2>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-[#514c45]">
                    <li>Jawab jujur sesuai kebiasaan nyata.</li>
                    <li>Jawaban final hanya bisa dikirim satu kali.</li>
                    <li>Progress tersimpan otomatis saat kamu mengisi.</li>
                </ul>
            </div>
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <h2 class="font-black text-[#191919]">Privasi</h2>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-[#514c45]">
                    <li>Jawabanmu dijaga sebagai data internal assessment.</li>
                    <li>Assessment ini bukan psikotes klinis.</li>
                    <li>Tidak digunakan untuk diagnosis psikologis, medis, atau mental health.</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('assessment.start') }}" class="mt-8">
            @csrf
            @error('access_code')
                <div class="mb-4 rounded-md border border-[#e5b4b4] bg-[#fff1f1] p-3 text-sm font-semibold text-[#8f1d1d]">
                    {{ $message }}
                </div>
            @enderror
            @if (! $accessCode->assigned_name || ! $accessCode->assigned_discord_id)
                <div class="mb-4 rounded-md border border-[#e5b4b4] bg-[#fff1f1] p-3 text-sm font-semibold text-[#8f1d1d]">
                    Kode assessment ini belum memiliki data peserta lengkap. Hubungi admin Squad Limpul.
                </div>
            @endif
            <button type="submit" @disabled(! $accessCode->assigned_name || ! $accessCode->assigned_discord_id) class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#191919] px-5 text-sm font-bold text-white transition hover:bg-[#303030] disabled:cursor-not-allowed disabled:opacity-50">
                Start assessment
            </button>
        </form>
    </section>
</x-layouts.participant>
