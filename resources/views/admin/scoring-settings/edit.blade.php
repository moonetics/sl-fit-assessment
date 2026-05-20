<x-layouts.admin title="Scoring Settings">
    <section class="rounded-lg border border-[#d7cfbf] bg-white p-5">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Admin-only config</p>
        <h1 class="mt-2 text-3xl font-black">Scoring thresholds</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#5d5850]">
            Nilai ini dipakai scorer saat result dibuat. Jika setting belum ada, sistem memakai fallback dari config bawaan.
        </p>

        <form method="POST" action="{{ route('admin.scoring-settings.update') }}" class="mt-6 grid gap-4 md:grid-cols-3">
            @csrf
            @method('PATCH')
            @foreach ($thresholds as $key => $value)
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">{{ str_replace('_', ' ', $key) }}</span>
                    <input name="{{ $key }}" value="{{ old($key, $value) }}" type="number" step="0.01" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3">
                    <span class="mt-1 block text-xs text-[#6b665d]">Default: {{ $defaults[$key] ?? '-' }}</span>
                </label>
            @endforeach
            <div class="md:col-span-3">
                <button class="h-11 rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Save thresholds</button>
            </div>
        </form>
    </section>
</x-layouts.admin>
