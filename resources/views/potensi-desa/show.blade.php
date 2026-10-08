@extends('layouts.app', ['title' => $potential->judul])

@php
    $images = $potential->media->filter(fn ($media) => $media->tipe_media === \App\Enums\TipeMediaPotensi::Image);
    $videos = $potential->media->filter(fn ($media) => $media->tipe_media === \App\Enums\TipeMediaPotensi::Video);
@endphp

@section('content')
    <a href="{{ route('potensi-desa.index') }}" class="back-link">← Kembali ke daftar potensi</a>
    <div class="mt-6 flex flex-col gap-5 border-b border-stone-200 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3"><p class="eyebrow">{{ $potential->category->nama }}</p><span class="{{ $potential->status->badgeClass() }}">{{ $potential->status->label() }}</span></div>
            <h1 class="page-title">{{ $potential->judul }}</h1>
            <p class="mt-4 text-sm text-stone-500">{{ $potential->village->nama }}, Kecamatan {{ $potential->village->kecamatan->nama }} · diperbarui {{ $potential->updated_at->diffForHumans() }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $potential)<a href="{{ route('potensi-desa.edit', $potential) }}" class="btn-secondary">Ubah data</a>@endcan
            @can('delete', $potential)
                <form action="{{ route('potensi-desa.destroy', $potential) }}" method="POST" onsubmit="return confirm('Arsipkan potensi ini? Media tetap disimpan untuk menjaga riwayat.');">@csrf @method('DELETE')<button class="inline-flex min-h-11 items-center justify-center border border-red-200 bg-white px-5 py-2.5 text-sm font-bold text-red-700 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700">Arsipkan</button></form>
            @endcan
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="border border-stone-200 bg-white p-6 sm:p-8">
                <p class="eyebrow">Cerita potensi</p>
                <div class="mt-5 whitespace-pre-line text-sm leading-7 text-stone-700">{{ $potential->deskripsi }}</div>
            </section>

            <section class="border border-stone-200 bg-white p-6 sm:p-8">
                <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Galeri</p><h2 class="section-title">Foto & video</h2></div><p class="text-sm text-stone-500">{{ $potential->media->count() }} media</p></div>
                @if ($potential->media->isEmpty())
                    <div class="mt-6 border border-dashed border-stone-300 bg-stone-50 px-5 py-10 text-center text-sm text-stone-500">Belum ada media pendukung.</div>
                @else
                    @if ($images->isNotEmpty())
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($images as $image)
                                <article class="overflow-hidden border border-stone-200 bg-stone-50">
                                    <img src="{{ route('potensi-desa.media.image', [$potential, $image]) }}" alt="{{ $image->judul ?: 'Foto '.$potential->judul }}" class="aspect-[4/3] w-full object-cover">
                                    <div class="p-4"><p class="font-semibold text-stone-900">{{ $image->judul ?: 'Foto potensi' }}</p><p class="mt-1 text-xs text-stone-500">Urutan {{ $image->urutan }}</p>
                                        @can('manageMedia', $potential)<form action="{{ route('potensi-desa.media.destroy', [$potential, $image]) }}" method="POST" class="mt-3">@csrf @method('DELETE')<button class="table-action-danger">Hapus foto</button></form>@endcan
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                    @if ($videos->isNotEmpty())
                        <div class="mt-6 space-y-3">
                            @foreach ($videos as $video)
                                <div class="flex flex-col gap-3 border border-stone-200 bg-stone-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0"><p class="font-semibold text-stone-900">{{ $video->judul ?: 'Video pendukung' }}</p><a href="{{ $video->media_url }}" target="_blank" rel="noopener noreferrer" class="mt-1 block truncate text-sm text-emerald-800 underline underline-offset-4">Buka video eksternal</a></div>
                                    @can('manageMedia', $potential)<form action="{{ route('potensi-desa.media.destroy', [$potential, $video]) }}" method="POST">@csrf @method('DELETE')<button class="table-action-danger">Hapus</button></form>@endcan
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </section>

            @can('manageMedia', $potential)
                <section class="border border-stone-200 bg-white p-6 sm:p-8">
                    <p class="eyebrow">Tambah media</p>
                    <div class="mt-5 grid gap-8 lg:grid-cols-2">
                        <form action="{{ route('potensi-desa.media.store', $potential) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf <input type="hidden" name="tipe_media" value="IMAGE">
                            <div><label for="foto_judul" class="form-label">Judul foto</label><input id="foto_judul" name="judul" class="form-input" maxlength="255"><x-input-error name="judul" /></div>
                            <div><label for="file" class="form-label">File foto</label><input id="file" name="file" type="file" accept="image/jpeg,image/png,image/webp" class="form-input" required><x-input-error name="file" /></div>
                            <div><label for="foto_urutan" class="form-label">Urutan</label><input id="foto_urutan" name="urutan" type="number" min="0" max="10000" value="0" class="form-input" required><x-input-error name="urutan" /></div>
                            <button class="btn-primary">Unggah foto</button>
                        </form>
                        <form action="{{ route('potensi-desa.media.store', $potential) }}" method="POST" class="space-y-4 border-t border-stone-200 pt-7 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                            @csrf <input type="hidden" name="tipe_media" value="VIDEO">
                            <div><label for="video_judul" class="form-label">Judul video</label><input id="video_judul" name="judul" class="form-input" maxlength="255"></div>
                            <div><label for="media_url" class="form-label">URL video</label><input id="media_url" name="media_url" type="url" class="form-input" placeholder="https://…" required><x-input-error name="media_url" /></div>
                            <div><label for="video_urutan" class="form-label">Urutan</label><input id="video_urutan" name="urutan" type="number" min="0" max="10000" value="0" class="form-input" required></div>
                            <button class="btn-primary">Simpan video</button>
                        </form>
                    </div>
                </section>
            @endcan
        </div>

        <aside class="space-y-5">
            <section class="border border-stone-200 bg-white p-5">
                <p class="eyebrow">Investasi</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-stone-500">Kebutuhan</dt><dd class="mt-1 font-semibold text-stone-950">{{ $potential->kebutuhan_investasi !== null ? 'Rp '.number_format((float) $potential->kebutuhan_investasi, 0, ',', '.') : 'Belum ditentukan' }}</dd></div>
                    <div><dt class="text-stone-500">Data pendukung</dt><dd class="mt-1 whitespace-pre-line leading-6 text-stone-700">{{ $potential->data_pendukung ?: 'Belum tersedia.' }}</dd></div>
                </dl>
            </section>
            <section class="border border-stone-200 bg-white p-5">
                <p class="eyebrow">Keterhubungan</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-stone-500">BUMDes/BUMDesma</dt><dd class="mt-1 font-semibold text-stone-950">{{ $potential->bumdes?->nama ?? 'Tidak ditautkan' }}</dd></div>
                    <div><dt class="text-stone-500">Kawasan</dt><dd class="mt-1 text-stone-700">{{ $potential->village->kawasans->pluck('nama')->join(', ') ?: 'Belum terhubung' }}</dd></div>
                    <div><dt class="text-stone-500">Published at</dt><dd class="mt-1 text-stone-700">{{ $potential->published_at?->translatedFormat('d F Y, H.i') ?? '—' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
