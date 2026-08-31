<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2"><label for="kecamatan_id" class="form-label">Kecamatan</label><select id="kecamatan_id" name="kecamatan_id" required class="form-input"><option value="">Pilih kecamatan</option>@foreach($kecamatans as $item)<option value="{{ $item->id }}" @selected((int) old('kecamatan_id', $desa?->kecamatan_id) === $item->id)>{{ $item->nama }} · {{ $item->kabupaten->nama }}</option>@endforeach</select><x-input-error name="kecamatan_id" /></div>
    <div><label for="kode" class="form-label">Kode wilayah</label><input id="kode" name="kode" value="{{ old('kode', $desa?->kode) }}" required class="form-input" placeholder="35.19.13.2005"><x-input-error name="kode" /></div>
    <div><label for="nama" class="form-label">Nama desa</label><input id="nama" name="nama" value="{{ old('nama', $desa?->nama) }}" required class="form-input"><x-input-error name="nama" /></div>
</div>
