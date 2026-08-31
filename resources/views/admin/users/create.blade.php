@extends('layouts.app', ['title' => 'Tambah Pengguna'])

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('admin.users.index') }}" class="back-link">← Kembali ke pengguna</a>
        <p class="eyebrow mt-6">Administrasi akses</p><h1 class="page-title">Tambah pengguna</h1><p class="page-lead">Buat akun dan tetapkan kewenangannya sejak awal.</p>
        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-8 border border-stone-200 bg-white p-6 sm:p-8">@csrf
            @include('admin.users._form', ['managedUser' => null])
            <div class="mt-8 flex justify-end border-t border-stone-200 pt-6"><button class="btn-primary">Buat akun</button></div>
        </form>
    </div>
@endsection
