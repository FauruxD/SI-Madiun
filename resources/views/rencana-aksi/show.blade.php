@extends('layouts.app', ['title' => $actionPlan->program])

@section('content')
    <div class="flex flex-col gap-6 border-b border-stone-300 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ route('rencana-aksi.index', ['tahun' => $actionPlan->tahun]) }}" class="back-link">← Matrix rencana aksi</a>
            <p class="eyebrow mt-6">{{ $bumdes->nama }} · {{ $actionPlan->tahun }}</p>
            <h1 class="page-title max-w-4xl">{{ $actionPlan->program }}</h1>
            <p class="page-lead">{{ $actionPlan->kegiatan }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $actionPlan)<a href="{{ route('bumdes.rencana-aksi.edit', [$bumdes, $actionPlan]) }}" class="btn-primary">Ubah rencana</a>@endcan
            @can('delete', $actionPlan)
                <form action="{{ route('bumdes.rencana-aksi.destroy', [$bumdes, $actionPlan]) }}" method="POST" onsubmit="return confirm('Hapus rencana aksi ini?')">@csrf @method('DELETE')<button class="btn-secondary text-red-700">Hapus</button></form>
            @endcan
        </div>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="border border-stone-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Target</p><p class="mt-3 text-xl font-semibold text-stone-950">{{ $actionPlan->target_nilai !== null ? $actionPlan->target_nilai.' '.$actionPlan->target_satuan : 'Target deskriptif' }}</p></div>
        <div class="border border-stone-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Total realisasi</p><p class="mt-3 text-xl font-semibold text-stone-950">{{ $snapshot['total_realisasi'] ?? '—' }} {{ $snapshot['total_realisasi'] !== null ? $actionPlan->target_satuan : '' }}</p></div>
        <div class="border border-stone-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Capaian</p><p class="mt-3 text-xl font-semibold text-stone-950">{{ $snapshot['persentase_capaian'] !== null ? $snapshot['persentase_capaian'].'%' : 'Tidak terhitung' }}</p></div>
        <div class="border border-stone-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Status</p><p class="mt-3"><span class="{{ $snapshot['status']->badgeClass() }}">{{ $snapshot['status']->label() }}</span></p><p class="mt-2 text-xs text-stone-500">{{ $snapshot['periode_terisi'] }}/{{ $actionPlan->periode->maximumPeriods() }} periode terisi</p></div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(17rem,1fr)]">
        <section class="border border-stone-200 bg-white p-5 sm:p-7" aria-labelledby="realization-heading">
            <div><p class="eyebrow">BUM-F-009</p><h2 id="realization-heading" class="section-title">Realisasi {{ strtolower($actionPlan->periode->label()) }}</h2><p class="mt-2 text-sm leading-6 text-stone-600">Masukkan nilai tambahan pada periode ini, bukan total kumulatif tahun berjalan.</p></div>
            <div class="mt-6 space-y-4">
                @foreach ($periodNumbers as $periodNumber)
                    @php($realization = $realizationsByPeriod->get($periodNumber))
                    @php($formPrefix = 'periode-'.$periodNumber)
                    <article class="border border-stone-200 bg-stone-50 p-4 sm:p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div><p class="font-semibold text-stone-950">{{ $actionPlan->periode->periodLabel($periodNumber) }}</p><p class="mt-1 text-xs text-stone-500">{{ $realization ? 'Terakhir diperbarui '.$realization->updated_at->diffForHumans() : 'Belum diisi' }}</p></div>
                            @if ($realization)<span class="{{ $realization->status->badgeClass() }}">{{ $realization->status->label() }}</span>@else<span class="badge-neutral">Belum Mulai</span>@endif
                        </div>

                        @can('manageRealizations', $actionPlan)
                            <form method="POST" action="{{ $realization ? route('bumdes.rencana-aksi.realisasi.update', [$bumdes, $actionPlan, $realization]) : route('bumdes.rencana-aksi.realisasi.store', [$bumdes, $actionPlan]) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                                @csrf
                                @if ($realization) @method('PUT') @endif
                                <input type="hidden" name="periode_ke" value="{{ $periodNumber }}">
                                <div><label for="{{ $formPrefix }}-nilai" class="form-label">Nilai realisasi periode</label><input id="{{ $formPrefix }}-nilai" name="realisasi_nilai" type="number" min="0" step="0.01" value="{{ $realization?->realisasi_nilai }}" class="form-input" inputmode="decimal" placeholder="Opsional"></div>
                                <div><label for="{{ $formPrefix }}-status" class="form-label">Status progres</label><select id="{{ $formPrefix }}-status" name="status" class="form-input" required>@foreach ($manualStatuses as $status)<option value="{{ $status->value }}" @selected(($realization?->status ?? \App\Enums\AchievementStatus::InProgress) === $status)>{{ $status->label() }}</option>@endforeach</select></div>
                                <div><label for="{{ $formPrefix }}-tanggal" class="form-label">Tanggal realisasi</label><input id="{{ $formPrefix }}-tanggal" name="tanggal_realisasi" type="date" value="{{ $realization?->tanggal_realisasi?->format('Y-m-d') }}" class="form-input"></div>
                                <div><label for="{{ $formPrefix }}-catatan" class="form-label">Catatan</label><textarea id="{{ $formPrefix }}-catatan" name="catatan" rows="2" class="form-input" maxlength="5000">{{ $realization?->catatan }}</textarea></div>
                                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-4"><p class="text-xs text-stone-500">Kontribusi capaian: <strong class="text-stone-800">{{ $realization?->persentase_capaian !== null ? $realization->persentase_capaian.'%' : 'tidak terhitung' }}</strong></p><button class="btn-secondary">{{ $realization ? 'Perbarui periode' : 'Simpan periode' }}</button></div>
                            </form>
                        @else
                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3"><div><dt class="text-xs text-stone-500">Realisasi</dt><dd class="mt-1 font-semibold">{{ $realization?->realisasi_nilai ?? '—' }} {{ $realization?->realisasi_nilai !== null ? $actionPlan->target_satuan : '' }}</dd></div><div><dt class="text-xs text-stone-500">Capaian periode</dt><dd class="mt-1 font-semibold">{{ $realization?->persentase_capaian !== null ? $realization->persentase_capaian.'%' : '—' }}</dd></div><div><dt class="text-xs text-stone-500">Tanggal</dt><dd class="mt-1 font-semibold">{{ $realization?->tanggal_realisasi?->translatedFormat('d M Y') ?? '—' }}</dd></div></dl>
                            @if ($realization?->catatan)<p class="mt-3 text-sm leading-6 text-stone-600">{{ $realization->catatan }}</p>@endif
                        @endcan
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="space-y-5">
            <div class="border border-stone-200 bg-white p-5"><p class="eyebrow">Rincian target</p><dl class="mt-4 space-y-4 text-sm"><div><dt class="text-stone-500">Periode</dt><dd class="mt-1 font-semibold">{{ $actionPlan->periode->label() }}</dd></div><div><dt class="text-stone-500">Rentang</dt><dd class="mt-1 font-semibold">{{ $actionPlan->tanggal_mulai?->translatedFormat('d M Y') ?? '—' }} — {{ $actionPlan->tanggal_selesai?->translatedFormat('d M Y') ?? '—' }}</dd></div><div><dt class="text-stone-500">Deskripsi target</dt><dd class="mt-1 leading-6">{{ $actionPlan->target_deskripsi ?: '—' }}</dd></div></dl></div>
            <div class="border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-950"><p class="font-bold">Cara membaca capaian</p><p class="mt-2">Persentase adalah jumlah nilai setiap periode dibagi target. Angka di atas 100% tetap ditampilkan. Target kosong atau nol tidak dihitung.</p></div>
        </aside>
    </div>
@endsection
