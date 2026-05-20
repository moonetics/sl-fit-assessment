<x-layouts.participant title="Assessment Complete">
    <section class="rounded-lg border border-[#d7cfbf] bg-white p-6 text-center shadow-[0_20px_70px_rgba(38,31,15,0.08)] sm:p-10">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Submitted</p>
        <h1 class="mt-3 text-3xl font-black text-[#191919]">Assessment berhasil dikirim.</h1>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-[#5d5850]">
            Terima kasih sudah mengisi dengan jujur. Admin Squad Limpul akan meninjau hasilnya. Detail skor tidak ditampilkan di halaman peserta.
        </p>
        <a href="{{ route('landing') }}" class="mt-8 inline-flex min-h-12 items-center justify-center rounded-md bg-[#191919] px-5 text-sm font-bold text-white transition hover:bg-[#303030]">
            Back to home
        </a>
    </section>
</x-layouts.participant>
