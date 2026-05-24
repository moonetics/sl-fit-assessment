<x-layouts.admin title="Code Batches">
    <div class="grid gap-5 lg:grid-cols-[0.75fr_1.25fr]">
        <section class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h1 class="text-2xl font-black">Batch generate kode</h1>
            <form method="POST" action="{{ route('admin.codes.batch-store') }}" class="mt-5 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Batch name</span>
                    <input name="name" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3" placeholder="Recruitment May 2026">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Source</span>
                    <input name="source" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3" placeholder="Discord event / manual invite">
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-bold">Expired date</span>
                        <input name="expires_at" type="date" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3">
                    </label>
                </div>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Participants CSV</span>
                    <textarea name="participants_csv" required rows="8" class="w-full rounded-md border border-[#cfc6b6] p-3 text-sm" placeholder="Satu peserta per baris: Nama Lengkap, DiscordUserID">{{ old('participants_csv') }}</textarea>
                    @error('participants_csv')
                        <span class="mt-2 block text-sm font-semibold text-[#8f1d1d]">{{ $message }}</span>
                    @enderror
                </label>
                <button class="h-11 rounded-md bg-[#8f1d1d] px-4 text-sm font-bold text-white">Generate batch</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-lg border border-[#d7cfbf] bg-white">
            <div class="border-b border-[#e7e0d3] p-5">
                <h2 class="text-2xl font-black">Batches</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f7f5ef] text-xs uppercase tracking-[0.12em] text-[#6b665d]">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Source</th>
                            <th class="px-4 py-3">Codes</th>
                            <th class="px-4 py-3">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eee6d8]">
                        @forelse ($batches as $batch)
                            @php
                                $copyText = $batch->accessCodes
                                    ->map(function ($code): string {
                                        if (! $code->display_code) {
                                            return '';
                                        }

                                        if (! $code->assigned_name) {
                                            return $code->display_code;
                                        }

                                        return "{$code->display_code} - {$code->assigned_name} (".($code->assigned_discord_id ?? '-').')';
                                    })
                                    ->filter()
                                    ->implode("\n");
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-bold">{{ $batch->name }}</td>
                                <td class="px-4 py-3">{{ $batch->source ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold">{{ $batch->access_codes_count }}</p>
                                        @if ($copyText !== '')
                                            <button
                                                type="button"
                                                class="h-8 rounded-md border border-[#cfc6b6] px-3 text-xs font-bold text-[#191919] transition hover:bg-[#f7f5ef]"
                                                data-copy-all
                                                data-copy-text="{{ rawurlencode($copyText) }}"
                                            >
                                                Copy all
                                            </button>
                                        @endif
                                    </div>
                                    <div class="mt-2 max-h-28 space-y-1 overflow-y-auto text-xs text-[#6b665d]">
                                        @foreach ($batch->accessCodes as $code)
                                            <p>
                                                <span class="font-mono font-bold text-[#191919]">{{ $code->display_code }}</span>
                                                @if ($code->assigned_name)
                                                    - {{ $code->assigned_name }}
                                                    <span class="text-[#8a6d16]">({{ $code->assigned_discord_id ?? '-' }})</span>
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $batch->expires_at?->toDateString() ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-[#6b665d]">Belum ada batch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-[#e7e0d3] p-4">{{ $batches->links() }}</div>
        </section>
    </div>
</x-layouts.admin>
