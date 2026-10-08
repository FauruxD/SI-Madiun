@extends('layouts.app', ['title' => 'Tambah BUMDes'])

@section('content')
    <a href="{{ route('bumdes.index') }}" class="back-link">← Daftar BUMDes</a>
    <div class="mt-6 max-w-3xl"><p class="eyebrow">Data baru</p><h1 class="page-title">Tambah BUMDes/BUMDesma</h1><p class="page-lead">Catat identitas, legalitas dasar, dan desa asal. Data detail dikelola setelah profil dibuat.</p></div>
    <form action="{{ route('bumdes.store') }}" method="POST" class="mt-8 border border-stone-200 bg-white p-5 sm:p-8">@csrf @include('bumdes._form')</form>
@endsection
