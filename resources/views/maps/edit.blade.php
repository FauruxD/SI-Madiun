@extends('layouts.app', ['title' => 'Atur geometri peta'])

@section('content')
<a href="{{ route('peta.manage', ['kind' => $kind]) }}" class="back-link">← Kembali ke peta</a>
<p class="eyebrow mt-6">{{ $kind === 'kawasan' ? 'Batas kawasan' : 'Lokasi pada peta' }}</p>
<h1 class="page-title">{{ $kind === 'potensi' ? $entity->judul : $entity->nama }}</h1>
<p class="page-lead">Editor hanya memperbarui geometri. Status dan data profil tetap mengikuti modul asal.</p>
<form method="POST" action="{{ route('peta.geometry.update', [$kind, $entity->id]) }}" class="mt-8">
    @csrf @method('PUT')
    @include('maps.partials.editor', ['geometry' => $entity->geometri])
    <div class="mt-5 flex flex-wrap gap-3"><button data-map-save class="btn-primary" disabled>Simpan lokasi</button><a href="{{ route('peta.manage', ['kind' => $kind]) }}" class="btn-secondary">Batal</a></div>
</form>
@if($kind === 'kawasan')
<section class="mt-10 border border-stone-200 bg-white">
    <div class="section-heading border-b border-stone-200 p-5"><div><p class="eyebrow">Fasilitas & akses</p><h2 class="section-title">Titik informasi kawasan</h2></div><a href="{{ route('peta.information.create', $entity->id) }}" class="btn-primary">Tambah titik informasi</a></div>
    <ul class="divide-y divide-stone-100">@forelse($informationPoints as $point)<li class="flex flex-wrap items-center justify-between gap-3 p-5"><div class="min-w-0"><h3 class="text-sm font-semibold break-words">{{ $point->nama }}</h3><p class="mt-1 text-xs text-stone-500">{{ \App\Models\TitikInformasi::TYPES[$point->jenis] ?? 'Informasi' }} · {{ $point->is_active ? 'Aktif' : 'Nonaktif' }}</p></div><a href="{{ route('peta.information.edit', [$entity->id, $point->id]) }}" class="btn-secondary">Edit titik</a></li>@empty<li class="p-5 text-sm text-stone-500">Belum ada titik informasi kawasan.</li>@endforelse</ul>
    @if($informationPoints->hasPages())<div class="border-t border-stone-200 p-5">{{ $informationPoints->links() }}</div>@endif
</section>
@endif
@endsection
