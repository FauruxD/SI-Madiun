@extends('layouts.app', ['title' => 'Ubah Pengguna'])

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('admin.users.index') }}" class="back-link">← Kembali ke pengguna</a>
        <p class="eyebrow mt-6">Administrasi akses</p><h1 class="page-title">Ubah pengguna</h1><p class="page-lead">Perbarui identitas, status, role, atau cakupan desa.</p>
        <form action="{{ route('admin.users.update', $managedUser) }}" method="POST" class="mt-8 border border-stone-200 bg-white p-6 sm:p-8">@csrf @method('PUT')
            @include('admin.users._form')
            <div class="mt-8 flex justify-end border-t border-stone-200 pt-6"><button class="btn-primary">Simpan perubahan</button></div>
        </form>
    </div>
@endsection
