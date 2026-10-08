@php
    $selectedVillage = (int) old('desa_id', $potential->desa_id ?? $villages->first()?->id);
    $selectedBumdes = (int) old('bumdes_id', $potential->bumdes_id ?? 0);
    $selectedStatus = old('status', isset($potential) ? $potential->status->value : \App\Enums\PotensiStatus::Draft->value);
@endphp

<div class="space-y-8">
    <section>
        <p class="eyebrow">Informasi potensi</p>
        <div class="mt-4 grid gap-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label for="judul" class="form-label">Judul potensi <span class="text-red-700">*</span></label>
                <input id="judul" name="judul" value="{{ old('judul', $potential->judul ?? '') }}" class="form-input" maxlength="255" required>
                <x-input-error name="judul" />
            </div>
            <div>
                <label for="desa_id" class="form-label">Desa <span class="text-red-700">*</span></label>
                <select id="desa_id" name="desa_id" class="form-input" required>
                    @foreach ($villages as $village)
                        <option value="{{ $village->id }}" @selected($selectedVillage === $village->id)>{{ $village->nama }} · {{ $village->kecamatan->nama }}</option>
                    @endforeach
                </select>
                <x-input-error name="desa_id" />
            </div>
            <div>
                <label for="kategori_potensi_id" class="form-label">Kategori <span class="text-red-700">*</span></label>
                <select id="kategori_potensi_id" name="kategori_potensi_id" class="form-input" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('kategori_potensi_id', $potential->kategori_potensi_id ?? 0) === $category->id)>{{ $category->nama }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                    @endforeach
                </select>
                <x-input-error name="kategori_potensi_id" />
            </div>
            <div class="lg:col-span-2">
                <label for="deskripsi" class="form-label">Deskripsi <span class="text-red-700">*</span></label>
                <textarea id="deskripsi" name="deskripsi" rows="7" class="form-input" maxlength="10000" required>{{ old('deskripsi', $potential->deskripsi ?? '') }}</textarea>
                <x-input-error name="deskripsi" />
            </div>
        </div>
    </section>

    <section class="border-t border-stone-200 pt-8">
        <p class="eyebrow">Investasi</p>
        <div class="mt-4 grid gap-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label for="data_pendukung" class="form-label">Data pendukung investasi</label>
                <textarea id="data_pendukung" name="data_pendukung" rows="5" class="form-input" maxlength="10000" placeholder="Kapasitas produksi, akses pasar, kesiapan lahan, atau informasi pendukung lain.">{{ old('data_pendukung', $potential->data_pendukung ?? '') }}</textarea>
                <x-input-error name="data_pendukung" />
            </div>
            <div>
                <label for="kebutuhan_investasi" class="form-label">Kebutuhan investasi (Rp)</label>
                <input id="kebutuhan_investasi" name="kebutuhan_investasi" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('kebutuhan_investasi', $potential->kebutuhan_investasi ?? '') }}" class="form-input" placeholder="Contoh: 250000000">
                <x-input-error name="kebutuhan_investasi" />
            </div>
        </div>
    </section>

    <section class="border-t border-stone-200 pt-8">
        <p class="eyebrow">Pengelola & publikasi</p>
        <div class="mt-4 grid gap-6 lg:grid-cols-2">
            <div>
                <label for="bumdes_id" class="form-label">BUMDes/BUMDesma terkait</label>
                <select id="bumdes_id" name="bumdes_id" class="form-input">
                    <option value="">Tidak ditautkan</option>
                    @foreach ($bumdesOptions as $bumdes)
                        <option value="{{ $bumdes->id }}" @selected($selectedBumdes === $bumdes->id)>{{ $bumdes->nama }} · {{ $bumdes->desas->pluck('nama')->join(', ') }}</option>
                    @endforeach
                </select>
                <x-input-error name="bumdes_id" />
                <p class="mt-2 text-xs leading-5 text-stone-500">Pilihan harus terkait dengan desa potensi; termasuk BUMDesma yang mencakup desa tersebut.</p>
            </div>
            @if (isset($potential) && $potential->eproposal !== null)
                <div>
                    <p class="form-label">Status publikasi</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">Status dikelola melalui workflow E-Proposal, bukan form potensi.</p>
                    <a href="{{ route('admin.eproposal.show', $potential->eproposal) }}" class="table-action mt-2 inline-block">Buka proposal</a>
                </div>
            @elseif (auth()->user()->isSuperAdmin())
                <div>
                    <label for="status" class="form-label">Status publikasi</label>
                    <select id="status" name="status" class="form-input">
                        @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach
                    </select>
                    <x-input-error name="status" />
                    <p class="mt-2 text-xs leading-5 text-stone-500">Publikasi manual hanya untuk katalog awal tanpa E-Proposal. Potensi dengan proposal wajib mengikuti workflow verifikasi.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="border-t border-stone-200 pt-8">
        <p class="eyebrow">Media</p>
        <p class="mt-3 text-sm leading-6 text-stone-600">Foto dan URL video dapat ditambahkan setelah data potensi disimpan.</p>
    </section>
</div>
