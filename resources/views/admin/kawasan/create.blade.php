@extends('layouts.app', ['title' => 'Tambah Kawasan'])

@section('content')
    <a href="{{ route('admin.kawasan.index') }}" class="back-link">← Kembali ke daftar kawasan</a>
    <div class="mt-6 max-w-5xl"><p class="eyebrow">Kawasan baru</p><h1 class="page-title">Bentuk kawasan perdesaan</h1><p class="page-lead">Susun informasi dasar, tipologi, desa anggota, dan penanggung jawab dalam satu proses atomik.</p></div>
    <form action="{{ route('admin.kawasan.store') }}" method="POST" class="mt-8 space-y-6">@csrf @include('admin.kawasan._form', ['kawasan' => null])<div class="flex flex-wrap gap-3"><button class="btn-primary">Simpan kawasan</button><a href="{{ route('admin.kawasan.index') }}" class="btn-secondary">Batal</a></div></form>
@endsection
