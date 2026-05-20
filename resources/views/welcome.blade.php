<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Squad Limpul Fit Assessment</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#f7f5ef] text-[#191919] antialiased">
        <div class="min-h-screen">
            <header class="border-b border-[#ded8cb] bg-[#fffdf7]/95">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3" aria-label="Squad Limpul home">
                        <img src="{{ asset('logo/sl-logo.png') }}" alt="Squad Limpul logo" class="size-12 rounded-md object-contain">
                        <span>
                            <span class="block text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Squad Limpul</span>
                            <span class="block text-xs text-[#6b665d]">Community Fit Assessment</span>
                        </span>
                    </a>
                    <a href="#code-entry" class="hidden min-h-10 items-center rounded-md bg-[#191919] px-4 text-sm font-bold text-white sm:inline-flex">
                        Enter code
                    </a>
                </div>
            </header>

            <main>
                <section class="bg-[#fffdf7]">
                    <div class="mx-auto grid min-h-[calc(100vh-81px)] max-w-6xl items-center gap-10 px-5 py-10 sm:px-8 lg:grid-cols-[1fr_0.82fr]">
                        <div>
                            <div class="mb-6 inline-flex items-center gap-3 rounded-md border border-[#d9c774] bg-[#fff8d8] px-3 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#765b08]">
                                <span class="size-2 rounded-full bg-[#8f1d1d]"></span>
                                Non-clinical assessment portal
                            </div>
                            <h1 class="max-w-3xl text-4xl font-black leading-[1.04] text-[#151515] sm:text-5xl lg:text-6xl">
                                Squad Limpul Fit Assessment
                            </h1>
                            <p class="mt-5 max-w-2xl text-base leading-7 text-[#4f4b45] sm:text-lg">
                                Portal ini membantu admin Squad Limpul meninjau kecocokan calon member dengan budaya komunitas: sopan di chat, sportif saat bermain, menerima aturan, dan tetap nyaman untuk member casual maupun competitive.
                            </p>
                            <div class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                                <div class="rounded-md border border-[#e4dccd] bg-white p-4">
                                    <p class="text-2xl font-black">76</p>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-[#6b665d]">Pertanyaan singkat</p>
                                </div>
                                <div class="rounded-md border border-[#e4dccd] bg-white p-4">
                                    <p class="text-2xl font-black">Auto</p>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-[#6b665d]">Progress tersimpan</p>
                                </div>
                                <div class="rounded-md border border-[#e4dccd] bg-white p-4">
                                    <p class="text-2xl font-black">Aman</p>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-[#6b665d]">Tidak dipublikasikan</p>
                                </div>
                            </div>
                        </div>

                        <div id="code-entry">
                            <div class="rounded-lg border border-[#d7cfbf] bg-white p-5 shadow-[0_24px_80px_rgba(38,31,15,0.12)] sm:p-6">
                                <div class="mb-5 flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-2xl font-black text-[#191919]">Mulai assessment</h2>
                                        <p class="mt-2 text-sm leading-6 text-[#6b665d]">
                                            Masukkan kode yang diberikan admin Squad Limpul. Setelah valid, kamu akan melihat welcome page dan aturan singkat sebelum mulai.
                                        </p>
                                    </div>
                                    <span class="rounded-md bg-[#f9d65c] px-3 py-1 font-mono text-xs font-black text-[#191919]">SLFA</span>
                                </div>

                                <form method="POST" action="{{ route('code.validate') }}" class="space-y-4">
                                    @csrf
                                    <label class="block">
                                        <span class="mb-2 block text-sm font-bold text-[#34312d]">Assessment code</span>
                                        <input name="access_code" value="{{ old('access_code') }}" type="text" placeholder="SLFA-XXXX-XXXX" class="h-12 w-full rounded-md border border-[#cfc6b6] bg-[#fffdf7] px-4 font-mono text-sm uppercase outline-none transition placeholder:text-[#9a948a] focus:border-[#191919] focus:ring-4 focus:ring-[#f9d65c]/35">
                                        @error('access_code')
                                            <span class="mt-2 block text-sm font-semibold text-[#8f1d1d]">{{ $message }}</span>
                                        @enderror
                                    </label>
                                    <button type="submit" class="h-12 w-full rounded-md bg-[#8f1d1d] px-4 text-sm font-bold text-white transition hover:bg-[#741616]">
                                        Continue
                                    </button>
                                </form>

                                <div class="mt-6 rounded-md border border-[#e7e0d3] bg-[#f7f5ef] p-4">
                                    <p class="text-sm font-black text-[#191919]">Catatan penting</p>
                                    <p class="mt-2 text-xs leading-5 text-[#6b665d]">
                                        Assessment ini bukan psikotes klinis dan tidak digunakan untuk diagnosis psikologis, medis, atau mental health. Hasil hanya menjadi alat bantu admin untuk review komunitas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-[#ded8cb] bg-[#fffdf7] px-5 py-5 sm:px-8">
                <div class="mx-auto flex max-w-6xl flex-col gap-2 text-sm text-[#6b665d] sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-semibold text-[#191919]">Managed Squad Limpul</p>
                    <p>&copy; {{ now()->year }} Squad Limpul Community Fit Assessment</p>
                </div>
            </footer>
        </div>
    </body>
</html>
