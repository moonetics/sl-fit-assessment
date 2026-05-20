<x-layouts.admin title="Question Bank">
    <div class="mb-6 flex flex-col justify-between gap-3 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Question bank</p>
            <h1 class="mt-2 text-3xl font-black">Assessment Questions</h1>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-[#6b665d]">
                Read-only admin view untuk cek isi soal, metadata scoring, red flag, consistency check, dan SL Profile Code.
            </p>
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            'Active Questions' => $summary['active'],
            'Community Fit' => $summary['community_fit'],
            'Situational' => $summary['situational'],
            'Honesty Check' => $summary['consistency'],
            'SL Profile' => $summary['profile'],
        ] as $label => $value)
            <div class="rounded-lg border border-[#d7cfbf] bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#6b665d]">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-5 rounded-lg border border-[#d7cfbf] bg-white p-5">
        <h2 class="text-xl font-black">Filter</h2>
        <form method="GET" action="{{ route('admin.questions.index') }}" class="mt-4 grid gap-3 md:grid-cols-4">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search text/category/no" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">

            <select name="question_type" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <option value="">All types</option>
                @foreach (['likert', 'situational'] as $type)
                    <option value="{{ $type }}" @selected(($filters['question_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>

            <select name="category" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                @endforeach
            </select>

            <select name="scoring_direction" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <option value="">All scoring</option>
                @foreach ($scoringDirections as $direction)
                    <option value="{{ $direction }}" @selected(($filters['scoring_direction'] ?? '') === $direction)>{{ $direction }}</option>
                @endforeach
            </select>

            <select name="profile_axis" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <option value="">All profile axes</option>
                @foreach ($profileAxes as $axis)
                    <option value="{{ $axis }}" @selected(($filters['profile_axis'] ?? '') === $axis)>{{ $axis }}</option>
                @endforeach
            </select>

            <select name="active" class="h-11 rounded-md border border-[#cfc6b6] px-3 text-sm">
                <option value="">All active states</option>
                <option value="active" @selected(($filters['active'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['active'] ?? '') === 'inactive')>Inactive</option>
            </select>

            <label class="flex min-h-11 items-center gap-2 rounded-md border border-[#cfc6b6] px-3 text-sm font-semibold">
                <input type="checkbox" name="consistency_only" value="1" @checked((bool) ($filters['consistency_only'] ?? false))>
                Consistency only
            </label>

            <label class="flex min-h-11 items-center gap-2 rounded-md border border-[#cfc6b6] px-3 text-sm font-semibold">
                <input type="checkbox" name="red_flag_only" value="1" @checked((bool) ($filters['red_flag_only'] ?? false))>
                Red flag only
            </label>

            <div class="flex gap-2 md:col-span-4">
                <button class="h-11 rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Apply filters</button>
                <a href="{{ route('admin.questions.index') }}" class="inline-flex h-11 items-center rounded-md border border-[#cfc6b6] bg-white px-4 text-sm font-bold">Clear</a>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-lg border border-[#d7cfbf] bg-white">
        <div class="border-b border-[#e7e0d3] p-5">
            <h2 class="text-2xl font-black">Soal dan metadata</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[1500px] text-left text-sm">
                <thead class="bg-[#f7f5ef] text-xs uppercase tracking-[0.12em] text-[#6b665d]">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Question</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Scoring</th>
                        <th class="px-4 py-3">Options</th>
                        <th class="px-4 py-3">Map</th>
                        <th class="px-4 py-3">Flags</th>
                        <th class="px-4 py-3">Consistency</th>
                        <th class="px-4 py-3">Profile</th>
                        <th class="px-4 py-3">Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d8]">
                    @forelse ($questions as $question)
                        <tr class="align-top">
                            <td class="px-4 py-3 font-bold">{{ $question->display_order ?? '-' }}</td>
                            <td class="px-4 py-3 font-bold">Q{{ $question->question_number }}</td>
                            <td class="max-w-md px-4 py-3">
                                <p class="font-semibold leading-6">{{ $question->text }}</p>
                                @if ($question->admin_notes)
                                    <p class="mt-2 rounded-md bg-[#fff8db] p-2 text-xs font-semibold leading-5 text-[#765b08]">{{ $question->admin_notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-[#f7f5ef] px-2 py-1 text-xs font-bold">{{ $question->question_type }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $question->category }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-[#f7f5ef] px-2 py-1 text-xs font-bold">{{ $question->scoring_direction }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    @forelse (($question->public_options ?? []) as $value => $label)
                                        <p class="rounded-md border border-[#e7e0d3] px-2 py-1 text-xs">
                                            <span class="font-black">{{ $value }}</span>: {{ $label }}
                                        </p>
                                    @empty
                                        <span class="text-[#6b665d]">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse (($question->scoring_map ?? []) as $value => $score)
                                        <span class="rounded-md bg-[#f7f5ef] px-2 py-1 text-xs font-bold">{{ $value }}={{ $score }}</span>
                                    @empty
                                        <span class="text-[#6b665d]">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse (($question->red_flag_options ?? []) as $option)
                                        <span class="rounded-md bg-[#fff1f1] px-2 py-1 text-xs font-black text-[#8f1d1d]">{{ $option }}</span>
                                    @empty
                                        <span class="text-[#6b665d]">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($question->is_consistency_item || $question->consistency_check)
                                    <div class="space-y-1">
                                        <p class="rounded-md bg-[#fff8db] px-2 py-1 text-xs font-bold text-[#765b08]">{{ $question->consistency_check ?? 'consistency_item' }}</p>
                                        @if ($question->consistency_pair)
                                            <p class="text-xs text-[#6b665d]">Pair: {{ implode(', ', $question->consistency_pair) }}</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[#6b665d]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($question->profile_axis)
                                    <span class="rounded-md bg-[#eef8f7] px-2 py-1 text-xs font-black text-[#0f766e]">{{ $question->profile_axis }} / {{ $question->profile_pole }}</span>
                                @else
                                    <span class="text-[#6b665d]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-black {{ $question->is_active ? 'bg-[#eef8f7] text-[#0f766e]' : 'bg-[#fff1f1] text-[#8f1d1d]' }}">
                                    {{ $question->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-[#6b665d]">Tidak ada soal yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#e7e0d3] p-4">
            {{ $questions->links() }}
        </div>
    </section>
</x-layouts.admin>
