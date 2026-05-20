<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\CodeBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CodeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_name' => ['required', 'string', 'max:120'],
            'assigned_discord_id' => ['required', 'string', 'max:120', 'regex:/^[0-9]+$/'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $admin = $this->admin($request);
        $code = $this->uniqueDisplayCode();

        $accessCode = AccessCode::create([
            'code_hash' => hash('sha256', $code),
            'display_code' => $code,
            'assigned_name' => $validated['assigned_name'],
            'assigned_discord_id' => $validated['assigned_discord_id'],
            'status' => AccessCode::STATUS_UNUSED,
            'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
            'created_by' => $admin?->id,
        ]);

        $this->audit($admin, 'CODE_GENERATED', 'access_code', $accessCode->id, null, $accessCode->only(['display_code', 'assigned_name', 'assigned_discord_id', 'status', 'expires_at']));

        return redirect()
            ->route('admin.dashboard')
            ->with('status', "Kode {$code} berhasil dibuat.");
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'integer', 'min:2', 'max:200'],
            'participants_csv' => ['required', 'string', 'max:24000'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $admin = $this->admin($request);
        $participants = $this->participantsCsv($validated['participants_csv']);
        $quantity = count($participants);

        if ($quantity < 2 || $quantity > 200) {
            return back()
                ->withInput()
                ->withErrors(['participants_csv' => 'Isi 2 sampai 200 baris peserta dengan format Nama Lengkap, DiscordUserID.']);
        }

        $batch = DB::transaction(function () use ($validated, $admin, $participants, $quantity): CodeBatch {
            $batch = CodeBatch::create([
                'name' => $validated['name'],
                'source' => $validated['source'] ?? null,
                'quantity' => $quantity,
                'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
                'created_by' => $admin?->id,
            ]);

            for ($i = 0; $i < $quantity; $i++) {
                $code = $this->uniqueDisplayCode();
                $participant = $participants[$i];

                AccessCode::create([
                    'code_hash' => hash('sha256', $code),
                    'display_code' => $code,
                    'assigned_name' => $participant['name'],
                    'assigned_discord_id' => $participant['discord_id'],
                    'status' => AccessCode::STATUS_UNUSED,
                    'expires_at' => $batch->expires_at,
                    'created_by' => $admin?->id,
                    'code_batch_id' => $batch->id,
                ]);
            }

            return $batch;
        });

        $this->audit($admin, 'BATCH_CODES_GENERATED', 'code_batch', $batch->id, null, [
            'name' => $batch->name,
            'source' => $batch->source,
            'quantity' => $batch->quantity,
            'participants' => $participants,
        ]);

        return redirect()
            ->route('admin.batches.index')
            ->with('status', "Batch {$batch->name} berhasil membuat {$batch->quantity} kode.");
    }

    public function reset(Request $request, AccessCode $code): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($code->status === AccessCode::STATUS_COMPLETED) {
            return back()->withErrors(['reason' => 'Kode completed tidak bisa di-reset dari MVP dashboard.']);
        }

        $admin = $this->admin($request);
        $before = $code->toArray();

        DB::transaction(function () use ($code, $validated): void {
            $code->participant?->delete();
            $code->update([
                'status' => AccessCode::STATUS_UNUSED,
                'started_at' => null,
                'completed_at' => null,
                'locked_reason' => null,
                'submission_attempt_id' => null,
            ]);
        });

        $this->audit($admin, 'CODE_RESET', 'access_code', $code->id, $before, [
            ...$code->fresh()->toArray(),
            'reason' => $validated['reason'],
        ]);

        return back()->with('status', 'Kode berhasil di-reset.');
    }

    public function lock(Request $request, AccessCode $code): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $admin = $this->admin($request);
        $before = $code->toArray();

        $code->update([
            'status' => AccessCode::STATUS_LOCKED,
            'locked_reason' => $validated['reason'],
        ]);

        $this->audit($admin, 'CODE_LOCKED', 'access_code', $code->id, $before, $code->fresh()->toArray());

        return back()->with('status', 'Kode berhasil dikunci.');
    }

    public function unlock(Request $request, AccessCode $code): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([AccessCode::STATUS_UNUSED, AccessCode::STATUS_IN_PROGRESS])],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $admin = $this->admin($request);
        $before = $code->toArray();

        $code->update([
            'status' => $validated['status'],
            'locked_reason' => null,
        ]);

        $this->audit($admin, 'CODE_UNLOCKED', 'access_code', $code->id, $before, [
            ...$code->fresh()->toArray(),
            'reason' => $validated['reason'],
        ]);

        return back()->with('status', 'Kode berhasil dibuka.');
    }

    private function uniqueDisplayCode(): string
    {
        do {
            $code = 'SLFA-'.$this->randomSegment().'-'.$this->randomSegment();
        } while (AccessCode::query()->where('code_hash', hash('sha256', $code))->exists());

        return $code;
    }

    /**
     * @return array<int, string>
     */
    private function participantsCsv(string $csv): array
    {
        $participants = collect(preg_split('/\R/', $csv) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(function (string $line): ?array {
                $columns = array_map('trim', str_getcsv($line));

                if (count($columns) < 2 || $columns[0] === '' || $columns[1] === '' || ! ctype_digit($columns[1])) {
                    return null;
                }

                return [
                    'name' => $columns[0],
                    'discord_id' => $columns[1],
                ];
            });

        if ($participants->contains(null)) {
            return [];
        }

        return $participants
            ->values()
            ->all();
    }

    private function randomSegment(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $segment = '';

        for ($i = 0; $i < 4; $i++) {
            $segment .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $segment;
    }

    private function admin(Request $request): ?Admin
    {
        return Admin::query()->find($request->session()->get('admin_id'));
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function audit(?Admin $admin, string $action, string $entityType, string $entityId, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'actor_id' => $admin?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
        ]);
    }
}
