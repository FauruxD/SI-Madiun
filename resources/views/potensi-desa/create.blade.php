@extends('layouts.app', ['title' => 'Tambah Potensi Desa'])

@section('content')
    <a href="{{ route('potensi-desa.index') }}" class="back-link">← Kembali ke daftar potensi</a>
    <div class="mt-6"><p class="eyebrow">Potensi baru</p><h1 class="page-title">Tambah potensi desa</h1><p class="page-lead">Simpan informasi inti terlebih dahulu, lalu lengkapi foto dan video pada halaman detail.</p></div>
    <form action="{{ route('potensi-desa.store') }}" method="POST" class="mt-8 border border-stone-200 bg-white p-6 sm:p-8">
        @csrf
        @include('potensi-desa._form', ['potential' => null])
        <div class="mt-9 flex flex-wrap gap-3"><button class="btn-primary">Simpan potensi</button><a href="{{ route('potensi-desa.index') }}" class="btn-secondary">Batal</a></div>
    </form>
@endsection
