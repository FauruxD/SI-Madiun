@extends('layouts.app', ['title' => 'Tambah Kategori Potensi'])

@section('content')
    <a href="{{ route('admin.kategori-potensi.index') }}" class="back-link">← Kembali ke master kategori</a>
    <div class="mt-6"><p class="eyebrow">Master data baru</p><h1 class="page-title">Tambah kategori potensi</h1></div>
    <form action="{{ route('admin.kategori-potensi.store') }}" method="POST" class="mt-8 max-w-3xl border border-stone-200 bg-white p-6 sm:p-8">
        @csrf
        @include('admin.kategori-potensi._form', ['category' => null])
        <div class="mt-8 flex gap-3"><button class="btn-primary">Simpan kategori</button><a href="{{ route('admin.kategori-potensi.index') }}" class="btn-secondary">Batal</a></div>
    </form>
@endsection
