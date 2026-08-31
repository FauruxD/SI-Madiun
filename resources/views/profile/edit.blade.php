@extends('layouts.app', ['title' => 'Profil Saya'])

@section('content')
    <div class="max-w-3xl">
        <p class="eyebrow">Akun pribadi</p>
        <h1 class="page-title">Profil saya</h1>
        <p class="page-lead">Perbarui informasi kontak yang melekat pada akun Anda.</p>

        <form action="{{ route('profile.update') }}" method="POST" class="mt-8 border border-stone-200 bg-white p-6 sm:p-8">
            @csrf
            @method('PATCH')
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2"><label for="name" class="form-label">Nama lengkap</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-input"><x-input-error name="name" /></div>
                <div><label for="username" class="form-label">Username</label><input id="username" name="username" value="{{ old('username', $user->username) }}" required class="form-input"><x-input-error name="username" /></div>
                <div><label for="phone" class="form-label">Nomor telepon</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="08xxxxxxxxxx"><x-input-error name="phone" /></div>
                <div class="sm:col-span-2"><label for="email" class="form-label">Alamat email</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input"><x-input-error name="email" /></div>
            </div>
            <div class="mt-8 flex items-center justify-end border-t border-stone-200 pt-6"><button class="btn-primary">Simpan perubahan</button></div>
        </form>
    </div>
@endsection
