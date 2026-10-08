@extends('layouts.app', ['title' => 'Peta & Geospasial'])

@section('content')
<div class="section-heading"><div><p class="eyebrow">Ruang kerja wilayah</p><h1 class="page-title">Peta & geospasial</h1><p class="page-lead">Kelola lokasi dalam wilayah tugas Anda tanpa mengubah profil, status publikasi, atau penetapan kawasan.</p></div><a href="{{ route('peta.index') }}" class="btn-secondary">Lihat peta publik →</a></div>
<div class="mt-8">@include('maps.partials.explorer')</div>
<section class="mt-10 border border-stone-200 bg-white" aria-label="Objek yang dapat dikelola">
    <nav class="flex overflow-x-auto border-b border-stone-200" aria-label="Jenis lokasi">
        @foreach($kinds as $key => $label)<a href="{{ route('peta.manage', ['kind' => $key]) }}" @class(['tab-link', 'tab-link-active' => $kind === $key]) @if($kind === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach
    </nav>
    <form method="GET" action="{{ route('peta.manage') }}" class="flex flex-wrap items-end gap-3 p-5">
        <input type="hidden" name="kind" value="{{ $kind }}">
        <div class="min-w-0 flex-1"><label for="map-search" class="form-label">Cari {{ strtolower($kinds[$kind]) }}</label><input id="map-search" name="q" value="{{ request('q') }}" maxlength="120" class="form-input" placeholder="Nama atau judul"></div><button class="btn-secondary">Cari</button>
    </form>
    <ul class="divide-y divide-stone-100">
        @forelse($entities as $entity)
            <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4"><div class="min-w-0"><h2 class="text-sm font-semibold break-words">{{ $kind === 'potensi' ? $entity->judul : $entity->nama }}</h2><p class="mt-1 text-xs text-stone-500">{{ $entity->geometri ? 'Geometri sudah diisi' : 'Lokasi belum diisi' }}</p></div><a href="{{ route('peta.geometry.edit', [$kind, $entity->id]) }}" class="btn-secondary">Atur {{ $kind === 'kawasan' ? 'batas' : 'lokasi' }} →</a></li>
        @empty
            <li class="px-5 py-8 text-sm leading-6 text-stone-500">Tidak ada objek yang sesuai dengan pencarian dan wilayah tugas Anda.</li>
        @endforelse
    </ul>
    @if($entities->hasPages())<div class="border-t border-stone-200 p-5">{{ $entities->links() }}</div>@endif
</section>
@endsection
