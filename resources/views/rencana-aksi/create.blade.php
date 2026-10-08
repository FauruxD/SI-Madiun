@extends('layouts.app', ['title' => 'Tambah Rencana Aksi'])

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('rencana-aksi.index') }}" class="back-link">← Matrix rencana aksi</a>
        <p class="eyebrow mt-7">{{ $bumdes->nama }} · BUM-F-008</p>
        <h1 class="page-title">Tambah rencana aksi</h1>
        <p class="page-lead">Susun program tahunan, target terukur, dan ritme pembaruan realisasinya.</p>

        <form method="POST" action="{{ route('bumdes.rencana-aksi.store', $bumdes) }}" class="mt-8 border border-stone-200 bg-white p-5 sm:p-8">
            @csrf
            @include('rencana-aksi._form')
            <div class="mt-8 flex flex-wrap gap-3 border-t border-stone-200 pt-6">
                <button class="btn-primary">Simpan rencana</button>
                <a href="{{ route('rencana-aksi.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
