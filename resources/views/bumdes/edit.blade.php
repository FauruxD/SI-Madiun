@extends('layouts.app', ['title' => 'Ubah '.$bumdes->nama])

@section('content')
    <a href="{{ route('bumdes.show', $bumdes) }}" class="back-link">← Detail BUMDes</a>
    <div class="mt-6 max-w-3xl"><p class="eyebrow">Profil & legalitas</p><h1 class="page-title">Ubah {{ $bumdes->nama }}</h1><p class="page-lead">Perbarui data resmi tanpa mengubah data child yang dikelola pada tab detail.</p></div>
    <form action="{{ route('bumdes.update', $bumdes) }}" method="POST" class="mt-8 border border-stone-200 bg-white p-5 sm:p-8">@csrf @method('PUT') @include('bumdes._form')</form>
@endsection
