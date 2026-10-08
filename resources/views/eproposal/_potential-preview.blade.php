<section class="min-w-0 border border-stone-200 bg-white p-6 sm:p-8" aria-labelledby="potential-preview-heading">
    <p class="eyebrow">Data potensi</p>
    <h2 id="potential-preview-heading" class="section-title break-words">{{ $potential->judul }}</h2>
    <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2">
        <div><dt class="text-stone-500">Desa</dt><dd class="mt-1 font-semibold">{{ $potential->village->nama }}, Kecamatan {{ $potential->village->kecamatan->nama }}</dd></div>
        <div><dt class="text-stone-500">Kategori</dt><dd class="mt-1 font-semibold">{{ $potential->category->nama }}</dd></div>
        <div><dt class="text-stone-500">BUMDes/BUMDesma</dt><dd class="mt-1 font-semibold">{{ $potential->bumdes?->nama ?: 'Tidak ditautkan' }}</dd></div>
        <div><dt class="text-stone-500">Kawasan</dt><dd class="mt-1 font-semibold">{{ $potential->village->kawasans->pluck('nama')->join(', ') ?: 'Belum terhubung' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-stone-500">Kebutuhan investasi</dt><dd class="mt-1 font-semibold">{{ $potential->kebutuhan_investasi !== null ? 'Rp '.number_format((float) $potential->kebutuhan_investasi, 0, ',', '.') : 'Belum ditentukan' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-stone-500">Deskripsi</dt><dd class="mt-2 whitespace-pre-line break-words leading-7 text-stone-700">{{ $potential->deskripsi }}</dd></div>
    </dl>
    <div class="mt-6 border-l-2 border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-stone-700">
        <h3 class="font-semibold text-stone-950">Data pendukung</h3>
        <p class="mt-2 whitespace-pre-line break-words">{{ $potential->data_pendukung ?: 'Belum tersedia.' }}</p>
    </div>
</section>

<section class="min-w-0 border border-stone-200 bg-white p-6 sm:p-8" aria-labelledby="proposal-gallery-heading">
    <p class="eyebrow">Media pendukung</p>
    <h2 id="proposal-gallery-heading" class="section-title">Galeri potensi</h2>
    @if ($potential->media->isEmpty())
        <p class="mt-5 border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">Belum ada foto atau video pendukung.</p>
    @else
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ($potential->media->sortBy('urutan') as $media)
                <article class="min-w-0 overflow-hidden border border-stone-200 bg-stone-50">
                    @if ($media->tipe_media === \App\Enums\TipeMediaPotensi::Image)
                        <img src="{{ route('potensi-desa.media.image', [$potential, $media]) }}" alt="{{ $media->judul ?: 'Foto '.$potential->judul }}" width="800" height="600" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        <p class="break-words p-4 text-sm font-semibold">{{ $media->judul ?: 'Foto potensi' }}</p>
                    @else
                        <div class="p-4">
                            <p class="break-words text-sm font-semibold">{{ $media->judul ?: 'Video pendukung' }}</p>
                            <a href="{{ $media->media_url }}" target="_blank" rel="noopener noreferrer" class="table-action mt-3 inline-block">Buka video eksternal <span class="sr-only">(tab baru)</span></a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>
