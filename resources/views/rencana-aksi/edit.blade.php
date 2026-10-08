@extends('layouts.app', ['title' => 'Ubah Rencana Aksi'])

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('bumdes.rencana-aksi.show', [$bumdes, $actionPlan]) }}" class="back-link">← Detail rencana aksi</a>
        <p class="eyebrow mt-7">{{ $bumdes->nama }} · {{ $actionPlan->tahun }}</p>
        <h1 class="page-title">Ubah rencana aksi</h1>
        <p class="page-lead">Perubahan target numerik akan menghitung ulang kontribusi capaian setiap periode secara otomatis.</p>

        <form method="POST" action="{{ route('bumdes.rencana-aksi.update', [$bumdes, $actionPlan]) }}" class="mt-8 border border-stone-200 bg-white p-5 sm:p-8">
            @csrf
            @method('PUT')
            @include('rencana-aksi._form')
            <div class="mt-8 flex flex-wrap gap-3 border-t border-stone-200 pt-6">
                <button class="btn-primary">Simpan perubahan</button>
                <a href="{{ route('bumdes.rencana-aksi.show', [$bumdes, $actionPlan]) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
