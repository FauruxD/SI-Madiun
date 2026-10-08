@extends('layouts.public', ['title' => 'Peta Kawasan Perdesaan'])

@section('content')
<div class="mx-auto max-w-[88rem] px-4 pt-10 sm:px-7 lg:px-10">
    <div class="section-heading">
        <div><p class="eyebrow">Wilayah & peluang</p><h1 class="page-title">Madiun, dalam peta.</h1><p class="page-lead">Jelajahi kawasan perdesaan, kantor BUMDes, fasilitas penting, dan potensi desa yang telah dipublikasikan.</p></div>
        <a href="{{ route('katalog-potensi.index') }}" class="btn-secondary">Jelajahi katalog →</a>
    </div>
    <div class="mt-8">@include('maps.partials.explorer')</div>
    <p class="mt-4 text-xs leading-5 text-stone-500">Batas dan lokasi ditampilkan sesuai data pengelola, bukan penetapan batas administratif resmi. Kawasan draf dan potensi yang belum Published tidak ditampilkan.</p>
</div>
@endsection
