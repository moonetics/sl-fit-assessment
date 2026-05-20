<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? 'Squad Limpul Community Fit Assessment' }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#f7f5ef] text-[#191919] antialiased">
        <div class="min-h-screen">
            <header class="border-b border-[#ded8cb] bg-[#fffdf7]">
                <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 sm:px-8">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3">
                        <img src="{{ asset('logo/sl-logo.png') }}" alt="Squad Limpul logo" class="size-10 rounded-md object-contain">
                        <span>
                            <span class="block text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Squad Limpul</span>
                            <span class="block text-xs text-[#6b665d]">Community Fit Assessment</span>
                        </span>
                    </a>
                </div>
            </header>

            <main class="px-5 py-8 sm:px-8">
                <div class="mx-auto max-w-5xl">
                    {{ $slot }}
                </div>
            </main>

            <footer class="border-t border-[#ded8cb] bg-[#fffdf7] px-5 py-5 sm:px-8">
                <div class="mx-auto flex max-w-5xl flex-col gap-2 text-sm text-[#6b665d] sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-semibold text-[#191919]">Managed Squad Limpul</p>
                    <p>&copy; {{ now()->year }} Squad Limpul Community Fit Assessment</p>
                </div>
            </footer>
        </div>
        <script>
            (() => {
                const cookieName = 'sl_device_id';
                const hasDeviceCookie = document.cookie.split('; ').some((cookie) => cookie.startsWith(`${cookieName}=`));

                if (!hasDeviceCookie) {
                    const deviceId = crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`;
                    document.cookie = `${cookieName}=${deviceId}; path=/; max-age=31536000; samesite=lax`;
                }
            })();
        </script>
    </body>
</html>
