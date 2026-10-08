@extends('layouts.app', ['title' => $proposal->potential->judul])

@section('content')
    <a href="{{ route('admin.eproposal.index') }}" class="back-link">← Kembali ke E-Proposal</a>
    <div class="mt-6 flex flex-col gap-5 border-b border-stone-200 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <p class="eyebrow">{{ $proposal->potential->category->nama }} · {{ $proposal->potential->village->nama }}</p>
                <span class="{{ $proposal->status->badgeClass() }}">{{ $proposal->status->label() }}</span>
            </div>
            <h1 class="page-title break-words">{{ $proposal->potential->judul }}</h1>
            <p class="mt-3 text-sm leading-6 text-stone-500">Diajukan {{ $proposal->submitted_at?->translatedFormat('d F Y, H.i') ?: '—' }} oleh {{ $proposal->submitter?->name ?: 'Pengguna' }}</p>
        </div>
        @can('download', $proposal)
            <a class="btn-secondary shrink-0" href="{{ route('admin.eproposal.download', $proposal) }}">Unduh PDF</a>
        @endcan
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-6">
            @include('eproposal._potential-preview', ['potential' => $proposal->potential])
            @if (filled($proposal->catatan_pengaju))
                <section class="border border-stone-200 bg-white p-6 sm:p-8">
                    <h2 class="section-title">Catatan pengaju</h2>
                    <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-stone-700">{{ $proposal->catatan_pengaju }}</p>
                </section>
            @endif
        </div>
        <aside class="min-w-0 space-y-6">
            <section class="border border-stone-200 bg-white p-5">
                <h2 class="eyebrow">Timeline review</h2>
                <ol class="mt-5 space-y-5">
                    @forelse ($proposal->reviews->sortBy('id') as $review)
                        <li class="border-l-2 border-emerald-700 pl-4">
                            <p class="text-sm font-semibold">{{ $review->status_sesudah->label() }}</p>
                            <p class="mt-1 text-xs leading-5 text-stone-500">{{ $review->reviewed_at?->translatedFormat('d M Y, H.i') }} · {{ $review->reviewer?->name ?: 'Sistem' }}</p>
                            @if (filled($review->catatan))<p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-stone-700">{{ $review->catatan }}</p>@endif
                        </li>
                    @empty
                        <li class="text-sm text-stone-500">Belum ada riwayat review.</li>
                    @endforelse
                </ol>
            </section>

            @can('resubmit', $proposal)
                <section class="border border-amber-200 bg-amber-50 p-5">
                    <h2 class="eyebrow text-amber-900">Tindak lanjut</h2>
                    <p class="mt-3 text-sm leading-6 text-amber-950">Perbaiki data sesuai catatan reviewer, lalu kirim ulang untuk direview.</p>
                    <a href="{{ route('potensi-desa.edit', $proposal->potential) }}" class="table-action mt-3 inline-block">Perbaiki data potensi</a>
                    <form action="{{ route('admin.eproposal.resubmit', $proposal) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <label for="catatan_pengaju" class="form-label">Catatan pengiriman ulang (opsional)</label>
                        <textarea id="catatan_pengaju" name="catatan_pengaju" rows="3" maxlength="5000" class="form-input">{{ old('catatan_pengaju', $proposal->catatan_pengaju) }}</textarea>
                        <x-input-error name="catatan_pengaju" />
                        <button class="btn-primary w-full">Kirim ulang</button>
                    </form>
                </section>
            @endcan

            @can('requestRevision', $proposal)
                <section class="border border-stone-200 bg-white p-5">
                    <h2 class="eyebrow">Review Admin Kawasan</h2>
                    <form action="{{ route('admin.eproposal.request-revision', $proposal) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <label for="catatan" class="form-label">Catatan revisi <span class="text-red-700">*</span></label>
                        <textarea id="catatan" name="catatan" rows="4" maxlength="5000" class="form-input" required>{{ old('catatan') }}</textarea>
                        <x-input-error name="catatan" />
                        <button class="btn-secondary w-full">Minta revisi</button>
                    </form>
                </section>
            @endcan
            @can('verify', $proposal)
                <form action="{{ route('admin.eproposal.verify', $proposal) }}" method="POST">@csrf<button class="btn-primary w-full">Verifikasi proposal</button></form>
            @endcan
            @can('publish', $proposal)
                <form action="{{ route('admin.eproposal.publish', $proposal) }}" method="POST">@csrf<button class="btn-primary w-full">Publish ke katalog</button></form>
            @endcan
        </aside>
    </div>
@endsection
