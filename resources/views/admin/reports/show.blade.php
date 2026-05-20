<x-layouts.admin title="PDF-ready Report">
    @php($result = $participant->result)
    @php($profileDetails = $result?->profile_breakdown['_profile'] ?? null)
    <section class="rounded-lg border border-[#d7cfbf] bg-white p-6">
        <div class="flex flex-col justify-between gap-3 border-b border-[#e7e0d3] pb-5 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">PDF-ready report</p>
                <h1 class="mt-2 text-3xl font-black">Assessment Result Summary</h1>
                <p class="mt-1 text-sm text-[#6b665d]">{{ $participant->display_name }} · Discord ID: {{ $participant->discord_user_id ?: $participant->discord_username }}</p>
            </div>
            <a href="{{ route('admin.participants.report.markdown', $participant) }}" class="inline-flex h-11 items-center rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Markdown</a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([
                'Community Fit' => $result?->community_fit_score ?? '-',
                'Competitive Fit' => $result?->competitive_fit_score ?? '-',
                'Risk Level' => $result?->risk_level ?? '-',
                'Honesty' => $result?->honesty_status ?? '-',
                'Member Type' => $result?->member_type ?? '-',
                'Final Status' => $result?->final_status ?? '-',
                'SL Profile' => trim(($result?->profile_code ?? '-').' '.($result?->profile_name ? '· '.$result->profile_name : '')),
            ] as $label => $value)
                <div class="rounded-md bg-[#f7f5ef] p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#6b665d]">{{ $label }}</p>
                    <p class="mt-2 text-xl font-black">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if ($profileDetails)
            <div class="mt-6 rounded-md bg-[#f7f5ef] p-5">
                <h2 class="text-xl font-black">SL Profile Interpretation</h2>
                <p class="mt-2 text-sm leading-6 text-[#5d5850]">{{ $profileDetails['description'] ?? '-' }}</p>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div>
                        <p class="text-sm font-black">Best fit</p>
                        <p class="mt-1 text-sm leading-6 text-[#5d5850]">{{ $profileDetails['best_fit'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-black">Strengths</p>
                        <ul class="mt-1 space-y-1 text-sm leading-6 text-[#5d5850]">
                            @foreach (($profileDetails['strengths'] ?? []) as $strength)
                                <li>{{ $strength }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-black">Watchouts</p>
                        <ul class="mt-1 space-y-1 text-sm leading-6 text-[#5d5850]">
                            @foreach (($profileDetails['watchouts'] ?? []) as $watchout)
                                <li>{{ $watchout }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <p class="mt-4 rounded-md bg-[#fff8db] p-3 text-sm font-semibold leading-6 text-[#5d5850]">{{ $profileDetails['admin_guidance'] ?? '-' }}</p>
            </div>
        @endif

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <div>
                <h2 class="text-xl font-black">Risks & red flags</h2>
                <div class="mt-3 space-y-2 text-sm">
                    @foreach (($result?->risk_reasons ?? []) as $reason)
                        <p class="rounded-md bg-[#f7f5ef] p-3">{{ $reason }}</p>
                    @endforeach
                    @forelse (($result?->red_flags ?? []) as $flag)
                        <p class="rounded-md bg-[#fff1f1] p-3">{{ $flag['message'] ?? '-' }}</p>
                    @empty
                        <p class="rounded-md bg-[#f7f5ef] p-3">Tidak ada red flag berat.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h2 class="text-xl font-black">Admin context</h2>
                <div class="mt-3 space-y-2 text-sm">
                    @forelse ($participant->interviews as $interview)
                        <p class="rounded-md bg-[#f7f5ef] p-3">{{ $interview->outcome }} · {{ $interview->answers_summary }}</p>
                    @empty
                        <p class="rounded-md bg-[#f7f5ef] p-3">Belum ada interview.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-layouts.admin>
