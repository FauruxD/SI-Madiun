<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2"><label for="kabupaten_id" class="form-label">Kabupaten</label><select id="kabupaten_id" name="kabupaten_id" required class="form-input"><option value="">Pilih kabupaten</option>@foreach($kabupatens as $item)<option value="{{ $item->id }}" @selected((int) old('kabupaten_id', $kecamatan?->kabupaten_id) === $item->id)>{{ $item->nama }}</option>@endforeach</select><x-input-error name="kabupaten_id" /></div>
    <div><label for="kode" class="form-label">Kode wilayah</label><input id="kode" name="kode" value="{{ old('kode', $kecamatan?->kode) }}" required class="form-input" placeholder="35.19.13"><x-input-error name="kode" /></div>
    <div><label for="nama" class="form-label">Nama kecamatan</label><input id="nama" name="nama" value="{{ old('nama', $kecamatan?->nama) }}" required class="form-input"><x-input-error name="nama" /></div>
</div>
