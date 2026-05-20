<x-layouts.participant title="Assessment Question {{ $order }}">
    <style>
        .answer-input:checked + .answer-card {
            border-color: #0f766e;
            background: #ecfdf5;
            box-shadow: inset 0 0 0 1px #0f766e;
        }

        .answer-input:checked + .answer-card .answer-number {
            border-color: #0f766e;
            background: #0f766e;
            color: #ffffff;
        }

        .answer-input:checked + .answer-card .selected-badge {
            display: inline-flex;
        }

        .question-nav-legend-box {
            width: 14px;
            height: 14px;
            display: inline-block;
            border-radius: 4px;
            vertical-align: -2px;
        }

        .question-nav-legend-box.is-answered {
            border: 2px solid #0f766e;
            background: #0f766e;
        }

        .question-nav-legend-box.is-unanswered {
            border: 2px solid #cfc6b6;
            background: #ffffff;
        }
    </style>
    <section class="relative rounded-lg border border-[#d7cfbf] bg-white p-5 shadow-[0_20px_70px_rgba(38,31,15,0.08)] sm:p-8">
        @php($isCurrentAnswered = ! is_null($answerValue))
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Assessment</p>
                <h1 class="mt-2 text-2xl font-black text-[#191919]">Soal {{ $order }} dari {{ $total }}</h1>
            </div>
            <div class="flex min-h-8 items-center gap-2">
                <span id="autosave-status" class="invisible rounded-md border border-[#d7cfbf] bg-[#fffdf7] px-3 py-1 text-xs font-bold text-[#514c45]">
                    Tersimpan
                </span>
                <span class="rounded-md bg-[#f9d65c] px-3 py-1 text-sm font-black text-[#191919]">{{ $progress }}%</span>
            </div>
        </div>

        <div class="mt-5 h-3 overflow-hidden rounded-full bg-[#eee6d6]">
            <div class="h-full rounded-full bg-[#8f1d1d]" style="width: {{ $progress }}%"></div>
        </div>

        @error('answer_value')
            <div class="mt-5 rounded-md border border-[#e5b4b4] bg-[#fff1f1] p-3 text-sm font-semibold text-[#8f1d1d]">
                {{ $message }}
            </div>
        @enderror

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_250px]">
            <form
                method="POST"
                action="{{ route('assessment.questions.answer', ['order' => $order]) }}"
                data-participant-id="{{ $participantId }}"
                data-display-order="{{ $order }}"
                data-autosave-url="{{ route('api.answers.autosave') }}"
                data-csrf-token="{{ csrf_token() }}"
            >
                @csrf
                <input type="hidden" name="answer_started_at">
                <input type="hidden" name="client_duration_seconds" value="0">
                <input type="hidden" name="visibility_change_count" value="0">
                <input type="hidden" name="offline_sync_count" value="0">
                <fieldset>
                    <legend class="text-xl font-black leading-8 text-[#191919]">{{ $question['text'] }}</legend>

                    <div class="mt-6 grid gap-3">
                        @foreach ($question['public_options'] as $value => $label)
                            <label class="block cursor-pointer">
                                <input
                                    type="radio"
                                    name="answer_value"
                                    value="{{ $value }}"
                                    @checked((string) old('answer_value', $answerValue) === (string) $value)
                                    class="answer-input peer sr-only"
                                >
                                <span class="answer-card flex min-h-20 items-center gap-4 rounded-md border-2 border-[#d7cfbf] bg-[#fffdf7] p-4 transition peer-focus-visible:ring-4 peer-focus-visible:ring-[#0f766e]/25 hover:border-[#191919]">
                                    <span class="answer-number grid h-11 w-11 shrink-0 place-items-center rounded-md border border-[#cfc6b6] bg-white text-lg font-black text-[#191919] transition">
                                        {{ $value }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-base font-black leading-6 text-[#191919]">{{ $label }}</span>
                                    </span>
                                    <span class="selected-badge ml-auto hidden rounded-full bg-[#0f766e] px-3 py-1 text-xs font-black text-white">
                                        Dipilih
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @if (count($glossaryNotes) > 0)
                    <div class="mt-5 rounded-md border border-[#d9c774] bg-[#fff8db] p-4">
                        <p class="text-sm font-black text-[#765b08]">Catatan istilah</p>
                        <div class="mt-2 space-y-2 text-sm leading-6 text-[#514c45]">
                            @foreach ($glossaryNotes as $term => $note)
                                <p><span class="font-bold">{{ $term }}:</span> {{ $note }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="submit"
                        name="direction"
                        value="back"
                        @disabled($order <= 1)
                        class="inline-flex min-h-12 items-center justify-center rounded-md border border-[#cfc6b6] bg-white px-5 text-sm font-bold text-[#191919] transition enabled:hover:border-[#191919] disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Back
                    </button>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        @if (! $isCurrentAnswered)
                            <button type="submit" name="direction" value="skip" class="inline-flex min-h-12 items-center justify-center rounded-md border border-[#cfc6b6] bg-white px-5 text-sm font-bold text-[#191919] transition hover:border-[#191919]">
                                Lewati dulu
                            </button>
                        @endif
                        <button type="submit" name="direction" value="next" class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#191919] px-5 text-sm font-bold text-white transition hover:bg-[#303030]">
                            {{ $order >= $total ? 'Review answers' : 'Next' }}
                        </button>
                    </div>
                </div>
            </form>

            <aside class="rounded-md border border-[#d7cfbf] bg-[#fffdf7] p-4 lg:sticky lg:top-5 lg:self-start">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-black text-[#191919]">Navigasi soal</h2>
                    <span class="text-xs font-semibold text-[#6b665d]">{{ count($answeredOrders) }}/{{ $total }}</span>
                </div>
                <div class="question-nav-grid mt-4" style="display: grid; grid-template-columns: repeat(5, 38px); gap: 8px; justify-content: center;">
                    @for ($navOrder = 1; $navOrder <= $total; $navOrder++)
                        @php($isAnswered = $answeredOrders[$navOrder] ?? false)
                        @php($navStyle = match (true) {
                            $navOrder === $order => 'width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #191919; border-radius: 6px; background: #191919; color: #ffffff; font-size: 14px; font-weight: 900; line-height: 1; box-sizing: border-box;',
                            $isAnswered => 'width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #0f766e; border-radius: 6px; background: #0f766e; color: #ffffff; font-size: 14px; font-weight: 900; line-height: 1; box-sizing: border-box;',
                            default => 'width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #cfc6b6; border-radius: 6px; background: #ffffff; color: #34312d; font-size: 14px; font-weight: 900; line-height: 1; box-sizing: border-box;',
                        })
                        <a
                            href="{{ route('assessment.questions.show', ['order' => $navOrder]) }}"
                            aria-label="Buka soal {{ $navOrder }}"
                            class="@class([
                                'question-nav-link',
                                'is-current' => $navOrder === $order,
                                'is-answered' => $navOrder !== $order && $isAnswered,
                                'is-unanswered' => $navOrder !== $order && ! $isAnswered,
                            ])"
                            style="{{ $navStyle }}"
                        >
                            {{ $navOrder }}
                        </a>
                    @endfor
                </div>
                <div class="mt-4 grid gap-2 text-xs font-semibold text-[#6b665d]">
                    <p><span class="question-nav-legend-box is-answered mr-2"></span>Terisi</p>
                    <p><span class="question-nav-legend-box is-unanswered mr-2"></span>Belum terisi</p>
                </div>
            </aside>
        </div>
    </section>

    <script>
        (() => {
            const form = document.querySelector('form[data-autosave-url]');
            const status = document.getElementById('autosave-status');

            if (!form || !status) {
                return;
            }

            const participantId = form.dataset.participantId;
            const displayOrder = form.dataset.displayOrder;
            const autosaveUrl = form.dataset.autosaveUrl;
            const csrfToken = form.dataset.csrfToken;
            const draftKey = `sl-assessment:${participantId}:${displayOrder}`;
            const radios = [...form.querySelectorAll('input[name="answer_value"]')];
            const startedAt = new Date();
            const startedAtInput = form.querySelector('input[name="answer_started_at"]');
            const durationInput = form.querySelector('input[name="client_duration_seconds"]');
            const visibilityInput = form.querySelector('input[name="visibility_change_count"]');
            const offlineInput = form.querySelector('input[name="offline_sync_count"]');
            let visibilityChanges = 0;
            let offlineSyncCount = 0;

            startedAtInput.value = startedAt.toISOString();

            const telemetry = () => {
                const duration = Math.max(0, Math.round((Date.now() - startedAt.getTime()) / 1000));
                durationInput.value = String(duration);
                visibilityInput.value = String(visibilityChanges);
                offlineInput.value = String(offlineSyncCount);

                return {
                    answer_started_at: startedAt.toISOString(),
                    client_duration_seconds: duration,
                    visibility_change_count: visibilityChanges,
                    offline_sync_count: offlineSyncCount,
                };
            };

            const setStatus = (message, tone = 'neutral') => {
                status.textContent = message;
                status.classList.remove('invisible', 'border-[#d7cfbf]', 'bg-[#fffdf7]', 'text-[#514c45]', 'border-[#e5b4b4]', 'bg-[#fff1f1]', 'text-[#8f1d1d]', 'border-[#d9c774]', 'bg-[#fff6c7]', 'text-[#765b08]');

                if (tone === 'error') {
                    status.classList.add('border-[#e5b4b4]', 'bg-[#fff1f1]', 'text-[#8f1d1d]');
                    return;
                }

                if (tone === 'warning') {
                    status.classList.add('border-[#d9c774]', 'bg-[#fff6c7]', 'text-[#765b08]');
                    return;
                }

                status.classList.add('border-[#d7cfbf]', 'bg-[#fffdf7]', 'text-[#514c45]');
            };

            const storeDraft = (answerValue) => {
                localStorage.setItem(draftKey, JSON.stringify({
                    answer_value: answerValue,
                    client_saved_at: new Date().toISOString(),
                    telemetry: telemetry(),
                }));
            };

            const clearDraft = () => localStorage.removeItem(draftKey);

            const syncAnswer = async (answerValue, clientSavedAt = new Date().toISOString()) => {
                if (!navigator.onLine) {
                    storeDraft(answerValue);
                    setStatus('Offline', 'warning');
                    return;
                }

                try {
                    const response = await fetch(autosaveUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            display_order: Number(displayOrder),
                            answer_value: answerValue,
                            client_saved_at: clientSavedAt,
                            draft_id: draftKey,
                            ...telemetry(),
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Autosave failed');
                    }

                    clearDraft();
                    setStatus('Tersimpan');
                } catch (error) {
                    storeDraft(answerValue);
                    setStatus('Gagal menyimpan', 'error');
                }
            };

            const draft = localStorage.getItem(draftKey);

            if (draft) {
                try {
                    const parsed = JSON.parse(draft);
                    offlineSyncCount = parsed.telemetry?.offline_sync_count ?? 1;
                    const radio = radios.find((input) => input.value === String(parsed.answer_value));

                    if (radio) {
                        radio.checked = true;
                        syncAnswer(String(parsed.answer_value), parsed.client_saved_at);
                    }
                } catch (error) {
                    clearDraft();
                }
            }

            radios.forEach((radio) => {
                radio.addEventListener('change', () => {
                    syncAnswer(radio.value);
                });
            });

            document.addEventListener('visibilitychange', () => {
                visibilityChanges += 1;
                telemetry();
            });

            form.addEventListener('submit', () => telemetry());
            window.addEventListener('offline', () => {
                offlineSyncCount += 1;
                telemetry();
                setStatus('Offline', 'warning');
            });
            window.addEventListener('online', () => {
                const queuedDraft = localStorage.getItem(draftKey);

                if (!queuedDraft) {
                    return;
                }

                try {
                    const parsed = JSON.parse(queuedDraft);
                    offlineSyncCount = Math.max(offlineSyncCount, parsed.telemetry?.offline_sync_count ?? 1);
                    syncAnswer(String(parsed.answer_value), parsed.client_saved_at);
                } catch (error) {
                    clearDraft();
                }
            });
        })();
    </script>
</x-layouts.participant>
