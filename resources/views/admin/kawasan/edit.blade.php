@extends('layouts.app', ['title' => 'Ubah Kawasan'])

@section('content')
    <a href="{{ route('admin.kawasan.show', $kawasan) }}" class="back-link">← Kembali ke detail kawasan</a>
    <div class="mt-6 max-w-5xl"><p class="eyebrow">Pembaruan kawasan</p><h1 class="page-title">{{ $kawasan->nama }}</h1><p class="page-lead">Perubahan anggota, kategori, dan Admin Kawasan dicatat dalam log aktivitas.</p></div>
    <form action="{{ route('admin.kawasan.update', $kawasan) }}" method="POST" class="mt-8 space-y-6">@csrf @method('PUT') @include('admin.kawasan._form')<div class="flex flex-wrap gap-3"><button class="btn-primary">Simpan perubahan</button><a href="{{ route('admin.kawasan.show', $kawasan) }}" class="btn-secondary">Batal</a></div></form>
@endsection
