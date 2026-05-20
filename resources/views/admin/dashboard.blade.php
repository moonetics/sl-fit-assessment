<x-layouts.admin title="Admin Dashboard">
    <div class="grid gap-5 lg:grid-cols-[0.78fr_1.22fr]">
        <section class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h1 class="text-2xl font-black">Generate kode</h1>
            <p class="mt-2 text-sm leading-6 text-[#5d5850]">Kode default expired 7 hari jika tanggal tidak diisi.</p>
            <form method="POST" action="{{ route('admin.codes.store') }}" class="mt-5 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Participant full name</span>
                    <input name="assigned_name" value="{{ old('assigned_name') }}" required class="h-11 w-full rounded-md border border-[#cfc6b6] px-3" placeholder="Nama calon peserta">
                    @error('assigned_name')
                        <span class="mt-2 block text-sm font-semibold text-[#8f1d1d]">{{ $message }}</span>
                    @enderror
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Discord User ID</span>
                    <input name="assigned_discord_id" value="{{ old('assigned_discord_id') }}" required inputmode="numeric" pattern="[0-9]+" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3" placeholder="Contoh: 123456789012345678">
                    @error('assigned_discord_id')
                        <span class="mt-2 block text-sm font-semibold text-[#8f1d1d]">Discord User ID wajib berupa angka.</span>
                    @enderror
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold">Expired date</span>
                    <input name="expires_at" type="date" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3">
                </label>
                <button class="h-11 rounded-md bg-[#8f1d1d] px-4 text-sm font-bold text-white">Generate code</button>
            </form>
        </section>

        <section class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-2xl font-black">Filter</h2>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-5 grid gap-3 md:grid-cols-4">
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search code/name" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <select name="status" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                    <option value="">All code status</option>
                    @foreach ($codeStatuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="risk_level" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                    <option value="">All risk</option>
                    @foreach (['Low', 'Medium', 'High'] as $risk)
                        <option value="{{ $risk }}" @selected(($filters['risk_level'] ?? '') === $risk)>{{ $risk }}</option>
                    @endforeach
                </select>
                <select name="final_status" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                    <option value="">All final status</option>
                    @foreach ($finalStatuses as $status)
                        <option value="{{ $status }}" @selected(($filters['final_status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="batch_id" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                    <option value="">All batches</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? '') === $batch->id)>{{ $batch->name }}</option>
                    @endforeach
                </select>
                <input name="source" value="{{ $filters['source'] ?? '' }}" placeholder="Batch source" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <button class="h-11 rounded-md bg-[#191919] px-4 text-sm font-bold text-white md:col-span-2">Apply filters</button>
            </form>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-lg border border-[#d7cfbf] bg-white">
        <div class="border-b border-[#e7e0d3] p-5">
            <h2 class="text-2xl font-black">Kode dan peserta</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#f7f5ef] text-xs uppercase tracking-[0.12em] text-[#6b665d]">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Participant</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Batch</th>
                        <th class="px-4 py-3">Scores</th>
                        <th class="px-4 py-3">Risk</th>
                        <th class="px-4 py-3">Final</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d8]">
                    @forelse ($codes as $code)
                        @php($participant = $code->participant)
                        @php($result = $participant?->result)
                        <tr>
                            <td class="px-4 py-3 font-mono font-bold">{{ $code->display_code ?? 'Hidden' }}</td>
                            <td class="px-4 py-3">
                                @if ($participant)
                                    <p class="font-bold">{{ $participant->display_name }}</p>
                                    <p class="text-xs text-[#6b665d]">Discord ID: {{ $participant->discord_user_id ?: $participant->discord_username }}</p>
                                @elseif ($code->assigned_name)
                                    <p class="font-bold">{{ $code->assigned_name }}</p>
                                    <p class="text-xs text-[#6b665d]">Discord ID: {{ $code->assigned_discord_id ?? '-' }}</p>
                                @else
                                    <span class="text-[#6b665d]">Belum mulai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $code->status }}</td>
                            <td class="px-4 py-3">
                                <p>{{ $code->batch?->name ?? '-' }}</p>
                                <p class="text-xs text-[#6b665d]">{{ $code->batch?->source }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($result)
                                    CF {{ $result->community_fit_score }} / CP {{ $result->competitive_fit_score }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $result?->risk_level ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $result?->final_status ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($participant)
                                    <a href="{{ route('admin.participants.show', $participant) }}" class="font-bold text-[#8f1d1d]">Detail</a>
                                @else
                                    <form method="POST" action="{{ route('admin.codes.lock', $code) }}" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="reason" value="Locked from admin dashboard.">
                                        <button class="font-bold text-[#8f1d1d]">Lock</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-[#6b665d]">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#e7e0d3] p-4">
            {{ $codes->links() }}
        </div>
    </section>
</x-layouts.admin>
