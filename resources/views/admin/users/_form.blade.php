@php
    $selectedRole = old('role', $managedUser?->role->value ?? \App\UserRole::AdminDesa->value);
    $selectedDesas = collect(old('desa_ids', $managedUser?->desas->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2"><label for="name" class="form-label">Nama lengkap</label><input id="name" name="name" value="{{ old('name', $managedUser?->name) }}" required class="form-input"><x-input-error name="name" /></div>
    <div><label for="username" class="form-label">Username</label><input id="username" name="username" value="{{ old('username', $managedUser?->username) }}" required class="form-input"><x-input-error name="username" /></div>
    <div><label for="email" class="form-label">Alamat email</label><input id="email" type="email" name="email" value="{{ old('email', $managedUser?->email) }}" required class="form-input"><x-input-error name="email" /></div>
    <div><label for="phone" class="form-label">Nomor telepon</label><input id="phone" name="phone" value="{{ old('phone', $managedUser?->phone) }}" class="form-input" placeholder="Opsional"><x-input-error name="phone" /></div>
    <div><label for="role" class="form-label">Role</label><select id="role" name="role" required class="form-input" data-role-select>@foreach ($roles as $role)<option value="{{ $role->value }}" @selected($selectedRole === $role->value)>{{ $role->label() }}</option>@endforeach</select><x-input-error name="role" /></div>
    <div><label for="password" class="form-label">Kata sandi {{ $managedUser ? 'baru' : '' }}</label><input id="password" type="password" name="password" autocomplete="new-password" @required(! $managedUser) class="form-input"><p class="mt-1.5 text-xs text-stone-500">{{ $managedUser ? 'Kosongkan bila tidak diubah.' : 'Min. 10 karakter, huruf besar/kecil, dan angka.' }}</p><x-input-error name="password" /></div>
    <div><label for="password_confirmation" class="form-label">Ulangi kata sandi</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @required(! $managedUser) class="form-input"></div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="flex w-fit cursor-pointer items-center gap-3 border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-medium"><input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $managedUser?->is_active ?? true)) class="size-4 text-emerald-800 focus:ring-emerald-700">Akun aktif dan dapat masuk</label>
    </div>
</div>

<fieldset class="mt-8 border-t border-stone-200 pt-7" data-desa-assignments @if($selectedRole !== \App\UserRole::AdminDesa->value) hidden @endif>
    <legend class="text-base font-semibold">Penugasan desa</legend>
    <p class="mt-1 text-sm text-stone-500">Admin Desa hanya dapat membuka data desa yang dipilih.</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        @forelse ($desas as $desa)
            <label class="flex cursor-pointer items-start gap-3 border border-stone-200 px-4 py-3 text-sm hover:border-emerald-700"><input type="checkbox" name="desa_ids[]" value="{{ $desa->id }}" @checked($selectedDesas->contains($desa->id)) class="mt-0.5 size-4 text-emerald-800 focus:ring-emerald-700"><span><strong class="block">{{ $desa->nama }}</strong><span class="text-xs text-stone-500">{{ $desa->kecamatan->nama }} · {{ $desa->kode }}</span></span></label>
        @empty
            <p class="text-sm text-stone-500">Tambahkan master desa terlebih dahulu.</p>
        @endforelse
    </div>
    <x-input-error name="desa_ids" />
</fieldset>
