<x-layouts.admin title="Audit Log">
    <section class="overflow-hidden rounded-lg border border-[#d7cfbf] bg-white">
        <div class="border-b border-[#e7e0d3] p-5">
            <h1 class="text-2xl font-black">Audit log</h1>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#f7f5ef] text-xs uppercase tracking-[0.12em] text-[#6b665d]">
                    <tr>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Actor</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Entity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d8]">
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="px-4 py-3">{{ $log->created_at?->toDateTimeString() }}</td>
                            <td class="px-4 py-3">{{ $log->actor?->email ?? 'System' }}</td>
                            <td class="px-4 py-3 font-bold">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->entity_type }} · {{ $log->entity_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-[#6b665d]">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#e7e0d3] p-4">
            {{ $auditLogs->links() }}
        </div>
    </section>
</x-layouts.admin>
