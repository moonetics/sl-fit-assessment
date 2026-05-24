## Assessment Result Summary
@php($profileDetails = $result?->profile_breakdown['_profile'] ?? null)

- Nama Peserta: {{ $participant->display_name }}
- Discord User ID: {{ $participant->discord_user_id ?: $participant->discord_username }}
- Kode Peserta: {{ $participant->accessCode?->display_code ?? '-' }}
- Tanggal Submit: {{ $participant->accessCode?->completed_at?->toDateTimeString() ?? '-' }}
- Community Fit Score: {{ $result?->community_fit_score ?? '-' }}
- Competitive Fit Score: {{ $result?->competitive_fit_score ?? '-' }}
- Risk Level: {{ $result?->risk_level ?? '-' }}
- Honesty Status: {{ $result?->honesty_status ?? '-' }}
- Member Type: {{ $result?->member_type ?? '-' }}
- Final Status: {{ $result?->final_status ?? '-' }}
- SL Profile Code: {{ $result?->profile_code ?? '-' }}
- SL Profile Name: {{ $result?->profile_name ?? '-' }}
@if ($profileDetails)
- SL Profile Description: {{ $profileDetails['description'] ?? '-' }}
- SL Profile Best Fit: {{ $profileDetails['best_fit'] ?? '-' }}
- SL Profile Admin Guidance: {{ $profileDetails['admin_guidance'] ?? '-' }}
@endif
- SL Profile Note: Profile code adalah indikator gaya komunitas yang research-informed, bukan diagnosis, MBTI, label klinis, atau dasar otomatis final decision.

### Risk Reasons
@forelse (($result?->risk_reasons ?? []) as $reason)
- {{ $reason }}
@empty
- Belum ada alasan risk level tercatat.
@endforelse

### Profile Breakdown
@forelse (collect($result?->profile_breakdown ?? [])->except('_profile') as $axis)
- {{ $axis['selected_pole'] ?? '-' }} / {{ $axis['selected_label'] ?? '-' }} ({{ $axis['confidence'] ?? 'Balanced' }}): {{ $axis['summary'] ?? '-' }} {{ $axis['description'] ?? '' }}
@empty
- Belum ada profile breakdown.
@endforelse

@if ($profileDetails)
### Profile Strengths
@foreach (($profileDetails['strengths'] ?? []) as $strength)
- {{ $strength }}
@endforeach

### Profile Watchouts
@foreach (($profileDetails['watchouts'] ?? []) as $watchout)
- {{ $watchout }}
@endforeach
@endif

### Red Flags
@forelse (($result?->red_flags ?? []) as $flag)
- {{ $flag['message'] ?? 'Flag tercatat.' }}
@empty
- Tidak ada red flag berat.
@endforelse

### Suspicious Flags
@forelse (($result?->suspicious_flags ?? []) as $flag)
- {{ $flag['type'] ?? 'suspicious' }}: {{ $flag['message'] ?? 'Flag tercatat.' }}
@empty
- Tidak ada suspicious flag.
@endforelse

### Recommendation
Gunakan ringkasan ini sebagai alat bantu admin Squad Limpul, bukan keputusan klinis atau vonis mutlak.
