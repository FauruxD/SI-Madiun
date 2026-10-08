@php
    $selectedCategories = collect(old('category_ids', $kawasan?->categories->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
    $selectedDesas = collect(old('desa_ids', $kawasan?->desas->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
    $selectedAdmins = collect(old('admin_user_ids', $kawasan?->adminUsers->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
    $selectedStatus = old('status', $kawasan?->status->value ?? \App\Enums\KawasanStatus::Draft->value);
@endphp

<section class="border border-stone-200 bg-white p-5 sm:p-7">
    <div class="border-b border-stone-200 pb-5"><p class="eyebrow">Informasi dasar</p><h2 class="section-title">Identitas kawasan</h2></div>
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2"><label for="nama" class="form-label">Nama kawasan</label><input id="nama" name="nama" value="{{ old('nama', $kawasan?->nama) }}" required class="form-input" autocomplete="off"><x-input-error name="nama" /></div>
        <div class="sm:col-span-2"><label for="deskripsi" class="form-label">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="4" class="form-input">{{ old('deskripsi', $kawasan?->deskripsi) }}</textarea><x-input-error name="deskripsi" /></div>
        <div><label for="tanggal_penetapan" class="form-label">Tanggal penetapan</label><input id="tanggal_penetapan" type="date" name="tanggal_penetapan" value="{{ old('tanggal_penetapan', $kawasan?->tanggal_penetapan?->format('Y-m-d')) }}" class="form-input"><p class="mt-1.5 text-xs text-stone-500">Dapat dikosongkan bila belum tersedia.</p><x-input-error name="tanggal_penetapan" /></div>
        <div><label for="status" class="form-label">Status kawasan</label><select id="status" name="status" required class="form-input">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach</select><x-input-error name="status" /></div>
    </div>
</section>

<section class="border border-stone-200 bg-white p-5 sm:p-7">
    <div class="section-heading border-b border-stone-200 pb-5"><div><p class="eyebrow">Kategori / tipologi</p><h2 class="section-title">Karakter kawasan</h2></div><span class="text-sm text-stone-500">Pilih satu atau lebih</span></div>
    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($categories as $category)
            <label @class(['selection-card', 'opacity-65' => ! $category->is_active])><input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked($selectedCategories->contains($category->id)) @disabled(! $category->is_active && ! $selectedCategories->contains($category->id)) class="mt-0.5 size-4 shrink-0 text-emerald-800 focus:ring-emerald-700"><span><strong class="block text-stone-950">{{ $category->nama }}</strong><span class="mt-1 block text-xs text-stone-500">{{ $category->kode }}{{ $category->is_active ? '' : ' · nonaktif, relasi lama dipertahankan' }}</span></span></label>
        @empty
            <p class="text-sm text-stone-500">Belum ada kategori aktif. Tambahkan master kategori terlebih dahulu.</p>
        @endforelse
    </div>
    <x-input-error name="category_ids" />
    <x-input-error name="category_ids.*" />
</section>

<section class="border border-stone-200 bg-white p-5 sm:p-7" data-village-picker data-options-url="{{ route('admin.kawasan.options.desa') }}">
    <div class="section-heading border-b border-stone-200 pb-5"><div><p class="eyebrow">Desa anggota</p><h2 class="section-title">Cakupan wilayah</h2></div><div class="w-full sm:max-w-xs"><label for="desa-search" class="sr-only">Cari desa anggota</label><input id="desa-search" type="search" class="form-input mt-0" placeholder="Cari desa atau kecamatan…" data-village-search autocomplete="off"></div></div>
    <p class="mt-4 text-xs text-stone-500">Menampilkan maksimal 30 hasil pencarian dari database, ditambah seluruh desa yang sudah dipilih.</p>
    <div class="mt-5 grid max-h-[28rem] gap-3 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3" data-village-list>
        @forelse ($desas as $desa)
            <label class="selection-card"><input type="checkbox" name="desa_ids[]" value="{{ $desa->id }}" @checked($selectedDesas->contains($desa->id)) class="mt-0.5 size-4 shrink-0 text-emerald-800 focus:ring-emerald-700"><span><strong class="block text-stone-950">{{ $desa->nama }}</strong><span class="mt-1 block text-xs text-stone-500">{{ $desa->kecamatan->nama }} · {{ $desa->kode }}</span></span></label>
        @empty
            <p class="text-sm text-stone-500">Master desa belum tersedia.</p>
        @endforelse
    </div>
    <p hidden class="mt-4 border border-dashed border-stone-300 p-4 text-sm text-stone-500" data-village-empty>Tidak ada desa yang cocok.</p>
    <p class="mt-3 min-h-5 text-xs text-stone-500" role="status" aria-live="polite" data-village-status></p>
    <x-input-error name="desa_ids" />
    <x-input-error name="desa_ids.*" />
</section>

<section class="border border-stone-200 bg-white p-5 sm:p-7">
    <div class="border-b border-stone-200 pb-5"><p class="eyebrow">Admin Kawasan</p><h2 class="section-title">Penanggung jawab</h2></div>
    <p class="mt-4 text-sm leading-6 text-stone-600">Penugasan dapat dikosongkan, diganti, atau diberikan kepada lebih dari satu akun ber-role Admin Kawasan.</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        @forelse ($adminUsers as $admin)
            <label @class(['selection-card', 'opacity-65' => ! $admin->is_active])><input type="checkbox" name="admin_user_ids[]" value="{{ $admin->id }}" @checked($selectedAdmins->contains($admin->id)) class="mt-0.5 size-4 shrink-0 text-emerald-800 focus:ring-emerald-700"><span><strong class="block text-stone-950">{{ $admin->name }}</strong><span class="mt-1 block text-xs text-stone-500">{{ $admin->email }}{{ $admin->is_active ? '' : ' · akun nonaktif' }}</span></span></label>
        @empty
            <p class="text-sm text-stone-500">Belum ada akun Admin Kawasan.</p>
        @endforelse
    </div>
    <x-input-error name="admin_user_ids" />
    <x-input-error name="admin_user_ids.*" />
</section>

@if (! $kawasan)
    <aside class="border-l-4 border-amber-400 bg-amber-50 p-5 text-sm leading-6 text-amber-950">Dokumen SK Bupati dan TKPKP dapat diunggah setelah kawasan pertama kali disimpan.</aside>
@endif
