@php
    $isEdit = isset($bumdes);
    $selectedDesaIds = collect(old('desa_ids', $isEdit ? $bumdes->desas->modelKeys() : []))->map(fn ($id) => (int) $id)->all();
    $primaryVillage = old('desa_utama_id', $isEdit ? $bumdes->desas->firstWhere('pivot.is_desa_utama', true)?->id : null);
@endphp

<div class="grid gap-5 lg:grid-cols-2">
    <div class="lg:col-span-2"><label for="nama" class="form-label">Nama BUMDes/BUMDesma</label><input id="nama" name="nama" value="{{ old('nama', $bumdes->nama ?? '') }}" required class="form-input" autocomplete="organization"><x-input-error name="nama" /></div>
    @if (auth()->user()->isSuperAdmin())
        <div><label for="jenis" class="form-label">Jenis</label><select id="jenis" name="jenis" required class="form-input">@foreach ($jenisOptions as $jenis)<option value="{{ $jenis->value }}" @selected(old('jenis', isset($bumdes) ? $bumdes->jenis->value : \App\Enums\JenisBumdes::Bumdes->value) === $jenis->value)>{{ $jenis->label() }}</option>@endforeach</select><x-input-error name="jenis" /></div>
    @else
        <div><p class="form-label">Jenis</p><p class="form-input bg-stone-100">{{ $bumdes->jenis->label() }}</p></div>
    @endif
    <div><label for="nomor_registrasi" class="form-label">Nomor registrasi Kemendesa</label><input id="nomor_registrasi" name="nomor_registrasi" value="{{ old('nomor_registrasi', $bumdes->nomor_registrasi ?? '') }}" class="form-input" placeholder="Opsional"><x-input-error name="nomor_registrasi" /></div>
    <div><label for="tahun_berdiri" class="form-label">Tahun berdiri</label><input id="tahun_berdiri" type="number" min="1" max="{{ date('Y') }}" name="tahun_berdiri" value="{{ old('tahun_berdiri', $bumdes->tahun_berdiri ?? '') }}" class="form-input" inputmode="numeric"><x-input-error name="tahun_berdiri" /></div>
    <div><label for="status_hukum" class="form-label">Status hukum pendirian</label><input id="status_hukum" name="status_hukum" value="{{ old('status_hukum', $bumdes->status_hukum ?? '') }}" class="form-input" placeholder="Contoh: Perdes Nomor 2 Tahun 2020"><x-input-error name="status_hukum" /></div>
    <div><label for="telepon" class="form-label">Nomor telepon</label><input id="telepon" name="telepon" value="{{ old('telepon', $bumdes->telepon ?? '') }}" class="form-input" autocomplete="tel"><x-input-error name="telepon" /></div>
    <div><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" value="{{ old('email', $bumdes->email ?? '') }}" class="form-input" autocomplete="email"><x-input-error name="email" /></div>
    <div class="lg:col-span-2"><label for="alamat" class="form-label">Alamat</label><textarea id="alamat" name="alamat" rows="3" class="form-input">{{ old('alamat', $bumdes->alamat ?? '') }}</textarea><x-input-error name="alamat" /></div>
    <div class="lg:col-span-2"><label for="deskripsi" class="form-label">Deskripsi singkat</label><textarea id="deskripsi" name="deskripsi" rows="4" class="form-input">{{ old('deskripsi', $bumdes->deskripsi ?? '') }}</textarea><x-input-error name="deskripsi" /></div>
</div>

@if (auth()->user()->isSuperAdmin())
    <fieldset class="mt-8 border-t border-stone-200 pt-7" data-village-picker data-options-url="{{ route('admin.kawasan.options.desa') }}">
        <legend class="font-display text-2xl text-stone-950">Desa asal</legend>
        <p class="mt-2 text-sm leading-6 text-stone-600">BUMDes memilih satu desa. BUMDesma dapat memilih beberapa desa, dengan satu desa utama.</p>
        <div class="mt-5"><label for="village-search" class="form-label">Cari desa</label><input id="village-search" type="search" class="form-input" placeholder="Cari nama, kode, atau kecamatan…" data-village-search autocomplete="off"><p class="mt-2 text-xs text-stone-500" aria-live="polite" data-village-status>{{ $desas->count() }} desa ditampilkan.</p></div>
        <div class="mt-4 grid max-h-80 gap-2 overflow-y-auto border border-stone-200 bg-stone-50 p-3 sm:grid-cols-2" data-village-list>
            @foreach ($desas as $desa)
                <label class="selection-card"><input type="checkbox" name="desa_ids[]" value="{{ $desa->id }}" @checked(in_array($desa->id, $selectedDesaIds, true)) class="mt-0.5 size-4 shrink-0 text-emerald-800 focus:ring-emerald-700"><span><strong class="block text-stone-950">{{ $desa->nama }}</strong><span class="mt-1 block text-xs text-stone-500">{{ $desa->kecamatan->nama }} · {{ $desa->kode }}</span></span></label>
            @endforeach
        </div>
        <p class="mt-3 text-sm text-stone-500" data-village-empty @if($desas->isNotEmpty()) hidden @endif>Tidak ada desa yang cocok.</p>
        <x-input-error name="desa_ids" />
        <x-input-error name="desa_ids.*" />
        <div class="mt-5 max-w-xl"><label for="desa_utama_id" class="form-label">Desa utama</label><select id="desa_utama_id" name="desa_utama_id" class="form-input" data-primary-village data-value="{{ $primaryVillage }}"><option value="">Pilih desa terlebih dahulu</option>@foreach ($desas->whereIn('id', $selectedDesaIds) as $desa)<option value="{{ $desa->id }}" @selected((int) $primaryVillage === $desa->id)>{{ $desa->nama }}</option>@endforeach</select><x-input-error name="desa_utama_id" /></div>
    </fieldset>
@endif

<div class="mt-8 flex flex-wrap gap-3"><button class="btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Buat BUMDes' }}</button><a href="{{ $isEdit ? route('bumdes.show', $bumdes) : route('bumdes.index') }}" class="btn-secondary">Batal</a></div>
