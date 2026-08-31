<x-layouts.guest title="Lupa kata sandi">
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-800">Pemulihan akun</p>
    <h1 class="mt-3 font-display text-4xl leading-tight">Atur ulang kata sandi</h1>
    <p class="mt-3 text-sm leading-6 text-stone-600">Masukkan email terdaftar. Kami akan mengirimkan tautan pemulihan akun.</p>

    <form action="{{ route('password.email') }}" method="POST" class="mt-9 space-y-6">
        @csrf
        <div>
            <label for="email" class="form-label">Alamat email</label>
            <input id="email" name="email" value="{{ old('email') }}" type="email" autocomplete="email" autofocus required class="form-input" placeholder="nama@contoh.id">
            <x-input-error name="email" />
        </div>
        <button type="submit" class="btn-primary w-full">Kirim tautan pemulihan</button>
    </form>
    <a href="{{ route('login') }}" class="mt-6 inline-flex text-sm font-semibold text-emerald-800 underline-offset-4 hover:underline">← Kembali ke halaman masuk</a>
</x-layouts.guest>
