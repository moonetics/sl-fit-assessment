<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'SL Admin Dashboard' }}</title>
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#f6f4ee] text-[#171717] antialiased">
        <header class="border-b border-[#ded8cb] bg-[#fffdf7]">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-4 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo/sl-logo.png') }}" alt="Squad Limpul logo" class="size-10 rounded-md object-contain">
                    <span>
                        <span class="block text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Squad Limpul</span>
                        <span class="block text-xs text-[#6b665d]">Admin Dashboard</span>
                    </span>
                </a>
                @if (session('admin_id'))
                    <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold">
                        <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Dashboard</a>
                        <a href="{{ route('admin.questions.index') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Questions</a>
                        <a href="{{ route('admin.batches.index') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Batches</a>
                        <a href="{{ route('admin.scoring-settings.edit') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Scoring Settings</a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Audit log</a>
                        <a href="{{ route('admin.export.results') }}" class="rounded-md px-3 py-2 hover:bg-[#f0eadf]">Export CSV</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="rounded-md bg-[#191919] px-3 py-2 text-white">Logout</button>
                        </form>
                    </nav>
                @endif
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            @if (session('status'))
                <div class="mb-5 rounded-md border border-[#b8d8b8] bg-[#f1fff1] p-4 text-sm font-semibold text-[#236423]">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-md border border-[#e5b4b4] bg-[#fff1f1] p-4 text-sm font-semibold text-[#8f1d1d]">
                    {{ $errors->first() }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
