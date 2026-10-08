@extends('layouts.app', ['title' => 'Ubah Potensi Desa'])

@section('content')
    <a href="{{ route('potensi-desa.show', $potential) }}" class="back-link">← Kembali ke detail potensi</a>
    <div class="mt-6"><p class="eyebrow">Pembaruan potensi</p><h1 class="page-title">{{ $potential->judul }}</h1></div>
    <form action="{{ route('potensi-desa.update', $potential) }}" method="POST" class="mt-8 border border-stone-200 bg-white p-6 sm:p-8">
        @csrf @method('PUT')
        @include('potensi-desa._form')
        <div class="mt-9 flex flex-wrap gap-3"><button class="btn-primary">Simpan perubahan</button><a href="{{ route('potensi-desa.show', $potential) }}" class="btn-secondary">Batal</a></div>
    </form>
@endsection
