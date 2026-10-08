@php($rawGeometry = old('geometri', $geometry ?? null))
<div data-map-root data-map-editor="{{ $geometryType }}" data-tile-url="{{ config('map.tile_url') }}" data-tile-attribution="{{ config('map.tile_attribution') }}" class="min-w-0 border border-stone-200 bg-white">
    <input type="hidden" name="geometri" data-map-input value="{{ is_array($rawGeometry) ? json_encode($rawGeometry, JSON_THROW_ON_ERROR) : $rawGeometry }}">
    <div class="flex flex-wrap gap-2 border-b border-stone-200 p-4">
        <button type="button" data-map-add disabled class="btn-secondary">{{ $geometryType === 'Polygon' ? 'Tambah simpul di tengah peta' : 'Letakkan pin di tengah peta' }}</button>
        @if($geometryType === 'Polygon')
            <button type="button" data-map-finish disabled class="btn-secondary">Selesaikan poligon</button>
            <button type="button" data-map-undo disabled class="btn-secondary">Batalkan simpul terakhir</button>
            <button type="button" data-map-new disabled class="btn-secondary">Gambar ulang</button>
        @endif
        <button type="button" data-map-clear disabled class="btn-secondary">Kosongkan lokasi</button>
    </div>
    <div data-map-canvas class="geo-map relative isolate h-[380px] bg-stone-200 sm:h-[500px]" role="region" aria-label="Editor peta; gunakan panah untuk menggeser dan plus atau minus untuk zoom"></div>
    @if($geometryType === 'Polygon')
        <div class="flex flex-wrap items-end gap-3 border-t border-stone-200 p-4">
            <div class="min-w-0 flex-1"><label for="map-vertex" class="form-label">Simpul yang ingin dipindahkan</label><select id="map-vertex" data-map-vertex class="form-input" disabled><option value="">Belum ada simpul</option></select></div>
            <button type="button" data-map-move disabled class="btn-secondary">Pindahkan simpul ke tengah peta</button>
        </div>
    @endif
    <p data-map-status role="status" class="border-t border-stone-200 px-4 py-3 text-xs leading-5 text-stone-600">Memuat editor peta…</p>
    <p data-map-tile-error hidden role="status" class="border-t border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">Peta dasar tidak tersedia. Jangan menyimpan lokasi jika posisi belum dapat dipastikan.</p>
    <p class="border-t border-stone-200 px-4 py-3 text-xs leading-5 text-stone-500">{{ $geometryType === 'Polygon' ? 'Klik peta untuk menambahkan simpul, lalu selesaikan poligon. Geser simpul untuk mengoreksi batas. Satu batas luar, tanpa lubang, maksimal 500 simpul.' : 'Klik peta untuk meletakkan pin; geser pin untuk memperbaiki lokasi.' }} Alternatif keyboard: fokus peta, gunakan panah untuk menggeser, lalu tombol “di tengah peta”. Perubahan baru tersimpan setelah menekan Simpan.</p>
    <noscript><p class="bg-amber-50 p-4 text-sm text-amber-900">JavaScript diperlukan untuk editor peta. Geometri tidak berubah sebelum Anda menyimpan.</p></noscript>
</div>
