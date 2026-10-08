@extends('layouts.app', ['title' => 'Ubah Kategori Kawasan'])

@section('content')
    <a href="{{ route('admin.kategori-kawasan.index') }}" class="back-link">← Kembali ke master kategori</a><div class="mt-6"><p class="eyebrow">Pembaruan master data</p><h1 class="page-title">{{ $category->nama }}</h1></div>
    <form action="{{ route('admin.kategori-kawasan.update', $category) }}" method="POST" class="mt-8 max-w-3xl border border-stone-200 bg-white p-6 sm:p-8">@csrf @method('PUT') @include('admin.kategori-kawasan._form')<div class="mt-8 flex gap-3"><button class="btn-primary">Simpan perubahan</button><a href="{{ route('admin.kategori-kawasan.index') }}" class="btn-secondary">Batal</a></div></form>
@endsection
