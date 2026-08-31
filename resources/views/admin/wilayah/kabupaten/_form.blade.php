<div class="grid gap-6 sm:grid-cols-[12rem_minmax(0,1fr)]">
    <div><label for="kode" class="form-label">Kode wilayah</label><input id="kode" name="kode" value="{{ old('kode', $kabupaten?->kode) }}" required class="form-input" placeholder="35.19"><x-input-error name="kode" /></div>
    <div><label for="nama" class="form-label">Nama kabupaten</label><input id="nama" name="nama" value="{{ old('nama', $kabupaten?->nama) }}" required class="form-input" placeholder="Kabupaten Madiun"><x-input-error name="nama" /></div>
</div>
