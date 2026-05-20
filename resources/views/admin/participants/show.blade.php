<x-layouts.admin title="Participant Detail">
    @php($result = $participant->result)
    <div class="mb-6 flex flex-col justify-between gap-3 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Participant detail</p>
            <h1 class="mt-2 text-3xl font-black">{{ $participant->display_name }}</h1>
            <p class="mt-1 text-sm text-[#6b665d]">Discord ID: {{ $participant->discord_user_id ?: $participant->discord_username }} · {{ $participant->accessCode->display_code }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.participants.report', $participant) }}" class="inline-flex h-11 items-center rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Report</a>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex h-11 items-center rounded-md border border-[#cfc6b6] bg-white px-4 text-sm font-bold">Back to dashboard</a>
        </div>
    </div>

    @if ($result)
        @php($profileDetails = $result->profile_breakdown['_profile'] ?? null)
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                'Community Fit' => $result->community_fit_score,
                'Competitive Fit' => $result->competitive_fit_score,
                'Risk Score' => $result->risk_score,
                'Risk Level' => $result->risk_level,
                'Honesty' => $result->honesty_status,
            ] as $label => $value)
                <div class="rounded-lg border border-[#d7cfbf] bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#6b665d]">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-black">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <section class="mt-5 grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#6b665d]">SL Profile Code</p>
                <h2 class="mt-2 text-4xl font-black">{{ $result->profile_code ?? '-' }}</h2>
                <p class="mt-1 text-lg font-bold text-[#514c45]">{{ $result->profile_name ?? 'Community Candidate' }}</p>
                <p class="mt-3 text-sm leading-6 text-[#6b665d]">
                    {{ $profileDetails['description'] ?? 'Non-clinical community profile. Dipakai sebagai konteks admin, bukan dasar otomatis untuk menerima atau menolak peserta.' }}
                </p>
                @if ($profileDetails)
                    <div class="mt-4 rounded-md bg-[#f7f5ef] p-3 text-sm">
                        <p class="font-black text-[#191919]">Best fit</p>
                        <p class="mt-1 leading-6 text-[#5d5850]">{{ $profileDetails['best_fit'] ?? '-' }}</p>
                    </div>
                    <div class="mt-3 rounded-md bg-[#fff8db] p-3 text-sm">
                        <p class="font-black text-[#765b08]">Admin guidance</p>
                        <p class="mt-1 leading-6 text-[#5d5850]">{{ $profileDetails['admin_guidance'] ?? '-' }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <h2 class="text-xl font-black">Why this risk level?</h2>
                <div class="mt-4 space-y-2">
                    @forelse (($result->risk_reasons ?? []) as $reason)
                        <p class="rounded-md bg-[#f7f5ef] p-3 text-sm font-semibold text-[#514c45]">{{ $reason }}</p>
                    @empty
                        <p class="text-sm text-[#6b665d]">Belum ada alasan risk level tercatat.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-5 grid gap-5 lg:grid-cols-[1fr_0.85fr]">
            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <h2 class="text-xl font-black">Category breakdown</h2>
                <div class="mt-4 space-y-3">
                    @foreach (($result->category_scores ?? []) as $category => $score)
                        <div>
                            <div class="mb-1 flex justify-between text-sm font-semibold">
                                <span>{{ $category }}</span>
                                <span>{{ $score['score'] ?? 0 }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-[#eee6d8]">
                                <div class="h-2 rounded-full bg-[#f9d65c]" style="width: {{ $score['score'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <h2 class="text-xl font-black">Decision</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="font-semibold text-[#6b665d]">Member type</dt>
                        <dd class="text-right font-bold">{{ $result->member_type }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-semibold text-[#6b665d]">Auto final</dt>
                        <dd class="text-right font-bold">{{ $result->auto_final_status ?? $result->final_status }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-semibold text-[#6b665d]">Current final</dt>
                        <dd class="text-right font-bold">{{ $result->final_status }}</dd>
                    </div>
                </dl>
                <form method="POST" action="{{ route('admin.participants.final-status.update', $participant) }}" class="mt-5 space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="final_status" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 text-sm">
                        @foreach ($finalStatuses as $status)
                            <option value="{{ $status }}" @selected($result->final_status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <textarea name="override_reason" rows="3" placeholder="Alasan override wajib" class="w-full rounded-md border border-[#cfc6b6] p-3 text-sm"></textarea>
                    <button class="h-11 rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Save override</button>
                </form>
            </div>
        </section>

        <section class="mt-5 rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Profile breakdown</h2>
            @if ($profileDetails)
                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    <div class="rounded-md border border-[#d7cfbf] bg-[#fffdf7] p-4">
                        <h3 class="font-black">Profile strengths</h3>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-[#514c45]">
                            @foreach (($profileDetails['strengths'] ?? []) as $strength)
                                <li>{{ $strength }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-md border border-[#d7cfbf] bg-[#fffdf7] p-4">
                        <h3 class="font-black">Profile watchouts</h3>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-[#514c45]">
                            @foreach (($profileDetails['watchouts'] ?? []) as $watchout)
                                <li>{{ $watchout }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @forelse (collect($result->profile_breakdown ?? [])->except('_profile') as $axis)
                    <div class="rounded-md bg-[#f7f5ef] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#6b665d]">{{ $axis['label'] ?? 'Profile axis' }}</p>
                                <p class="mt-1 text-lg font-black">{{ $axis['selected_pole'] ?? '-' }} · {{ $axis['selected_label'] ?? '-' }}</p>
                            </div>
                            <span class="rounded-md bg-white px-2 py-1 text-xs font-black text-[#514c45]">{{ $axis['confidence'] ?? 'Balanced' }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[#5d5850]">{{ $axis['summary'] ?? '-' }}</p>
                        <p class="mt-2 text-sm leading-6 text-[#5d5850]">{{ $axis['description'] ?? '' }}</p>
                        <div class="mt-3 grid gap-2 text-xs md:grid-cols-2">
                            <div class="rounded-md bg-white p-3">
                                <p class="font-black text-[#0f766e]">Strengths</p>
                                <ul class="mt-2 space-y-1 leading-5 text-[#514c45]">
                                    @foreach (($axis['strengths'] ?? []) as $strength)
                                        <li>{{ $strength }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="rounded-md bg-white p-3">
                                <p class="font-black text-[#8f1d1d]">Watchouts</p>
                                <ul class="mt-2 space-y-1 leading-5 text-[#514c45]">
                                    @foreach (($axis['watchouts'] ?? []) as $watchout)
                                        <li>{{ $watchout }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <p class="mt-3 rounded-md bg-[#fff8db] p-3 text-xs font-semibold leading-5 text-[#5d5850]">{{ $axis['admin_guidance'] ?? '-' }}</p>
                        @if (! empty($axis['scores']))
                            <p class="mt-2 text-xs font-semibold text-[#6b665d]">Scores: {{ collect($axis['scores'])->map(fn ($score, $pole) => $pole.'='.$score)->implode(' / ') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-[#6b665d]">Belum ada profile breakdown.</p>
                @endforelse
            </div>
        </section>

        <section class="mt-5 grid gap-5 lg:grid-cols-2">
            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <h2 class="text-xl font-black">Red flags</h2>
                <div class="mt-4 space-y-3">
                    @forelse (($result->red_flags ?? []) as $flag)
                        <div class="rounded-md bg-[#fff1f1] p-3 text-sm">
                            <p class="font-bold text-[#8f1d1d]">{{ $flag['severity'] ?? 'flag' }} · Q{{ $flag['question_number'] ?? '-' }}</p>
                            <p class="mt-1 text-[#5d5850]">{{ $flag['message'] ?? '-' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#6b665d]">Tidak ada red flag.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
                <h2 class="text-xl font-black">Suspicious activity</h2>
                <div class="mt-4 space-y-3">
                    @forelse (($result->suspicious_flags ?? []) as $flag)
                        <div class="rounded-md bg-[#fff8db] p-3 text-sm">
                            <p class="font-bold text-[#765b08]">{{ $flag['severity'] ?? 'flag' }} · {{ $flag['type'] ?? '-' }}</p>
                            <p class="mt-1 text-[#5d5850]">{{ $flag['message'] ?? '-' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#6b665d]">Tidak ada suspicious flag.</p>
                    @endforelse
                </div>
            </div>
        </section>
    @else
        <section class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Belum ada result</h2>
            <p class="mt-2 text-sm text-[#6b665d]">Participant belum submit final atau scoring belum berjalan.</p>
        </section>
    @endif

    <section class="mt-5 grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Session safety</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt>Devices</dt><dd class="font-bold">{{ $participant->sessions->pluck('device_id')->unique()->count() }}</dd></div>
                <div class="flex justify-between"><dt>Refresh</dt><dd class="font-bold">{{ $participant->sessions->sum('refresh_count') }}</dd></div>
                <div class="flex justify-between"><dt>Resume</dt><dd class="font-bold">{{ $participant->sessions->sum('resume_count') }}</dd></div>
                <div class="flex justify-between"><dt>Code status</dt><dd class="font-bold">{{ $participant->accessCode->status }}</dd></div>
            </dl>

            <div class="mt-5 space-y-3">
                <form method="POST" action="{{ route('admin.codes.lock', $participant->accessCode) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reason" value="Locked from participant detail.">
                    <button class="h-10 rounded-md bg-[#8f1d1d] px-3 text-sm font-bold text-white">Lock code</button>
                </form>
                @if ($participant->accessCode->status === \App\Models\AccessCode::STATUS_LOCKED)
                    <form method="POST" action="{{ route('admin.codes.unlock', $participant->accessCode) }}" class="flex gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="reason" value="Unlocked from participant detail.">
                        <input type="hidden" name="status" value="{{ \App\Models\AccessCode::STATUS_IN_PROGRESS }}">
                        <button class="h-10 rounded-md border border-[#cfc6b6] bg-white px-3 text-sm font-bold">Unlock</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Admin notes</h2>
            <form method="POST" action="{{ route('admin.participants.notes.store', $participant) }}" class="mt-4 space-y-3">
                @csrf
                <textarea name="note" rows="3" class="w-full rounded-md border border-[#cfc6b6] p-3 text-sm" placeholder="Tulis catatan internal"></textarea>
                <button class="h-10 rounded-md bg-[#191919] px-3 text-sm font-bold text-white">Add note</button>
            </form>
            <div class="mt-5 space-y-3">
                @forelse ($participant->notes->sortByDesc('created_at') as $note)
                    <div class="rounded-md bg-[#f7f5ef] p-3 text-sm">
                        <p class="font-semibold">{{ $note->note }}</p>
                        <p class="mt-1 text-xs text-[#6b665d]">{{ $note->admin?->email ?? 'System' }} · {{ $note->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#6b665d]">Belum ada catatan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-5 grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Interview module</h2>
            <form method="POST" action="{{ route('admin.participants.interviews.store', $participant) }}" class="mt-4 space-y-3">
                @csrf
                <input name="interviewer_name" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 text-sm" placeholder="Interviewer name">
                <input name="interview_at" type="datetime-local" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 text-sm">
                <select name="outcome" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 text-sm">
                    @foreach ($interviewOutcomes as $outcome)
                        <option value="{{ $outcome }}">{{ $outcome }}</option>
                    @endforeach
                </select>
                <textarea name="questions_summary" rows="3" class="w-full rounded-md border border-[#cfc6b6] p-3 text-sm" placeholder="Pertanyaan yang dibahas"></textarea>
                <textarea name="answers_summary" rows="3" class="w-full rounded-md border border-[#cfc6b6] p-3 text-sm" placeholder="Ringkasan jawaban dan konteks"></textarea>
                <button class="h-10 rounded-md bg-[#191919] px-3 text-sm font-bold text-white">Save interview</button>
            </form>
        </div>

        <div class="rounded-lg border border-[#d7cfbf] bg-white p-5">
            <h2 class="text-xl font-black">Interview history</h2>
            <div class="mt-4 space-y-3">
                @forelse ($participant->interviews->sortByDesc('created_at') as $interview)
                    <div class="rounded-md bg-[#f7f5ef] p-3 text-sm">
                        <p class="font-bold">{{ $interview->outcome }} · {{ $interview->interviewer_name }}</p>
                        <p class="mt-2 font-semibold">Q: {{ $interview->questions_summary }}</p>
                        <p class="mt-1 text-[#5d5850]">A: {{ $interview->answers_summary }}</p>
                        <p class="mt-2 text-xs text-[#6b665d]">{{ $interview->interview_at?->toDateTimeString() ?? $interview->created_at?->toDateTimeString() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-[#6b665d]">Belum ada interview.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-5 overflow-hidden rounded-lg border border-[#d7cfbf] bg-white">
        <div class="border-b border-[#e7e0d3] p-5">
            <h2 class="text-xl font-black">Answer review</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#f7f5ef] text-xs uppercase tracking-[0.12em] text-[#6b665d]">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Answer</th>
                        <th class="px-4 py-3">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d8]">
                    @foreach ($participant->answers->sortBy(fn ($answer) => $answer->question->question_number) as $answer)
                        <tr>
                            <td class="px-4 py-3 font-bold">Q{{ $answer->question->question_number }}</td>
                            <td class="px-4 py-3">{{ $answer->question->category }}</td>
                            <td class="px-4 py-3">{{ $answer->answer_value }}</td>
                            <td class="px-4 py-3">{{ $answer->score_value ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>
