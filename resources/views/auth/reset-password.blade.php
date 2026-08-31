<x-layouts.guest title="Kata sandi baru">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Pemulihan akun</p>
    <h1 class="mt-3 font-display text-4xl leading-tight">Buat kata sandi baru</h1>
    <p class="mt-3 text-sm leading-6 text-stone-600">Gunakan sedikitnya 10 karakter dengan huruf besar, huruf kecil, dan angka.</p>

    <form action="{{ route('password.update') }}" method="POST" class="mt-9 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="form-label">Alamat email</label>
            <input id="email" name="email" value="{{ old('email', $email) }}" type="email" autocomplete="email" required class="form-input">
            <x-input-error name="email" />
        </div>
        <div>
            <label for="password" class="form-label">Kata sandi baru</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required class="form-input">
            <x-input-error name="password" />
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-input">
        </div>
        <button type="submit" class="btn-primary w-full">Simpan kata sandi baru</button>
    </form>
</x-layouts.guest>
