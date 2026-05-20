<x-layouts.admin title="Admin Login">
    <section class="mx-auto max-w-md rounded-lg border border-[#d7cfbf] bg-white p-6 shadow-[0_20px_70px_rgba(38,31,15,0.08)]">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#8a6d16]">Admin access</p>
        <h1 class="mt-3 text-3xl font-black">Login admin</h1>

        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-bold">Email</span>
                <input name="email" value="{{ old('email') }}" type="email" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 outline-none focus:border-[#191919]">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-bold">Password</span>
                <input name="password" type="password" class="h-11 w-full rounded-md border border-[#cfc6b6] px-3 outline-none focus:border-[#191919]">
            </label>
            <button class="h-11 w-full rounded-md bg-[#191919] px-4 text-sm font-bold text-white">Login</button>
        </form>
    </section>
</x-layouts.admin>
