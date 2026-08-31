<x-layouts.guest title="Masuk">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Selamat datang kembali</p>
        <h1 class="mt-3 font-display text-4xl leading-tight">Masuk ke ruang kerja</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">Gunakan email atau username akun pengelola Anda.</p>
    </div>

    <form action="{{ route('login') }}" method="POST" class="mt-9 space-y-5">
        @csrf
        <div>
            <label for="identifier" class="form-label">Email atau username</label>
            <input id="identifier" name="identifier" value="{{ old('identifier') }}" type="text" autocomplete="username" autofocus required class="form-input" placeholder="nama@contoh.id">
            <x-input-error name="identifier" />
        </div>
        <div>
            <div class="flex items-center justify-between gap-4">
                <label for="password" class="form-label">Kata sandi</label>
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-800 underline-offset-4 hover:underline">Lupa kata sandi?</a>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input" placeholder="Masukkan kata sandi">
            <x-input-error name="password" />
        </div>
        <label class="flex w-fit cursor-pointer items-center gap-3 text-sm text-stone-700">
            <input type="checkbox" name="remember" value="1" class="size-4 border-stone-400 text-emerald-800 focus:ring-emerald-700">
            Ingat saya di perangkat ini
        </label>
        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>
</x-layouts.guest>
