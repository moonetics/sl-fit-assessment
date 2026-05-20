<x-layouts.participant title="Review Assessment">
    <section class="rounded-lg border border-[#d7cfbf] bg-white p-6 shadow-[0_20px_70px_rgba(38,31,15,0.08)] sm:p-8">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Final check</p>
        <h1 class="mt-3 text-3xl font-black text-[#191919]">Review jawaban</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-[#5d5850]">
            Pastikan semua soal sudah terjawab sebelum submit final. Setelah dikirim, jawaban tidak bisa diubah.
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <p class="text-3xl font-black text-[#191919]">{{ $answered }}</p>
                <p class="mt-1 text-sm font-semibold text-[#6b665d]">Terjawab</p>
            </div>
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <p class="text-3xl font-black text-[#191919]">{{ $missing }}</p>
                <p class="mt-1 text-sm font-semibold text-[#6b665d]">Belum terjawab</p>
            </div>
            <div class="rounded-md bg-[#f7f5ef] p-4">
                <p class="text-3xl font-black text-[#191919]">{{ $total }}</p>
                <p class="mt-1 text-sm font-semibold text-[#6b665d]">Total soal</p>
            </div>
        </div>

        @if ($missing > 0)
            <div class="mt-6 rounded-md border border-[#e5b4b4] bg-[#fff1f1] p-4">
                <p class="text-sm font-bold text-[#8f1d1d]">Masih ada soal kosong.</p>
                <p class="mt-2 text-sm leading-6 text-[#6b665d]">Kamu bisa kembali ke soal yang belum dijawab dari daftar di bawah. Submit final baru bisa dilakukan setelah semuanya lengkap.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($missingOrders as $missingOrder)
                        <a href="{{ route('assessment.questions.show', ['order' => $missingOrder]) }}" class="grid size-10 place-items-center rounded-md border border-[#e5b4b4] bg-white text-sm font-black text-[#8f1d1d] hover:border-[#8f1d1d]">
                            {{ $missingOrder }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('assessment.questions.show', ['order' => $firstMissingOrder]) }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md bg-[#8f1d1d] px-4 text-sm font-bold text-white">
                    Lengkapi soal pertama yang kosong
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('assessment.submit') }}" class="mt-8">
                @csrf
                <input type="hidden" name="submission_attempt_id" value="{{ $submissionAttemptId }}">
                <label class="flex items-start gap-3 rounded-md border border-[#d7cfbf] bg-[#fffdf7] p-4">
                    <input type="checkbox" name="final_confirmation" value="1" class="mt-1 size-4 accent-[#8f1d1d]">
                    <span class="text-sm leading-6 text-[#514c45]">Saya memahami jawaban final tidak bisa diubah.</span>
                </label>
                @error('final_confirmation')
                    <span class="mt-2 block text-sm font-semibold text-[#8f1d1d]">{{ $message }}</span>
                @enderror
                <button type="submit" class="mt-5 inline-flex min-h-12 items-center justify-center rounded-md bg-[#191919] px-5 text-sm font-bold text-white transition hover:bg-[#303030]">
                    Submit final
                </button>
            </form>
        @endif
    </section>
</x-layouts.participant>
