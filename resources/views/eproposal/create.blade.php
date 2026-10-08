@extends('layouts.app', ['title' => 'Ajukan E-Proposal'])

@section('content')
    <a href="{{ route('admin.eproposal.index') }}" class="back-link">← Kembali ke E-Proposal</a>
    <div class="mt-6 max-w-4xl">
        <p class="eyebrow">Pengajuan baru</p>
        <h1 class="page-title">Ajukan E-Proposal</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">Pilih potensi desa Anda, periksa data yang akan direview, lalu kirim proposal. Data inti tetap bersumber dari Potensi Desa tanpa disalin.</p>
    </div>

    <div class="mt-8 max-w-4xl space-y-6">
        <form action="{{ route('admin.eproposal.create') }}" method="GET" class="border border-stone-200 bg-white p-6 sm:p-8">
            <label for="potensi_desa_id" class="form-label">Potensi Desa</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <select id="potensi_desa_id" name="potensi_desa_id" class="form-input min-w-0 flex-1" required>
                    <option value="">Pilih potensi…</option>
                    @foreach ($potentials as $potential)
                        <option value="{{ $potential->id }}" @selected($selectedPotential?->id === $potential->id)>{{ $potential->judul }} — {{ $potential->village->nama }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary shrink-0" @disabled($potentials->isEmpty())>Lihat preview</button>
            </div>
            <x-input-error name="potensi_desa_id" />
            <p class="mt-3 text-xs leading-5 text-stone-500">Hanya potensi Draft tanpa proposal yang tersedia. Publikasi katalog lama tidak ditarik otomatis.</p>
            @if ($potentials->isEmpty())
                <p class="mt-4 text-sm text-stone-600">Belum ada potensi yang bisa diajukan. <a href="{{ route('potensi-desa.index') }}" class="table-action">Buka Potensi Desa</a></p>
            @endif
        </form>

        @if ($selectedPotential !== null)
            @include('eproposal._potential-preview', ['potential' => $selectedPotential])
            <form action="{{ route('admin.eproposal.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="potensi_desa_id" value="{{ $selectedPotential->id }}">
                <section class="border border-stone-200 bg-white p-6 sm:p-8">
                    <label for="catatan_pengaju" class="form-label">Catatan pengaju <span class="font-normal text-stone-500">(opsional)</span></label>
                    <textarea id="catatan_pengaju" name="catatan_pengaju" rows="5" maxlength="5000" class="form-input mt-2" placeholder="Konteks tambahan untuk Admin Kawasan…">{{ old('catatan_pengaju') }}</textarea>
                    <x-input-error name="catatan_pengaju" />
                </section>
                <div class="flex flex-wrap gap-3">
                    <button class="btn-primary">Kirim E-Proposal</button>
                    <a href="{{ route('potensi-desa.edit', $selectedPotential) }}" class="btn-secondary">Perbaiki data potensi</a>
                </div>
            </form>
        @else
            <p class="border border-dashed border-stone-300 p-6 text-sm text-stone-500">Pilih potensi dan buka preview untuk melanjutkan pengajuan.</p>
        @endif
    </div>
@endsection
