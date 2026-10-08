@extends('layouts.app', ['title' => 'Rencana Aksi & Realisasi'])

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">Kinerja BUMDes</p>
            <h1 class="page-title">Rencana aksi & realisasi</h1>
            <p class="page-lead">Pantau target tahunan, periode pelaporan, dan capaian setiap program pada BUMDes yang menjadi kewenangan Anda.</p>
        </div>
        @if (auth()->user()->isSuperAdmin() || auth()->user()->role === \App\UserRole::AdminDesa)
            <a href="{{ route('bumdes.index') }}" class="btn-primary">Pilih BUMDes</a>
        @endif
    </div>

    <form method="GET" class="mt-8 grid gap-3 border border-stone-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_10rem_auto]">
        <div>
            <label for="search" class="sr-only">Cari rencana aksi</label>
            <input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari BUMDes, program, atau kegiatan…">
        </div>
        <div>
            <label for="tahun" class="sr-only">Filter tahun</label>
            <select id="tahun" name="tahun" class="form-input mt-0">
                @foreach ($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" @selected($year === (int) $availableYear)>{{ $availableYear }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-secondary">Terapkan</button>
    </form>

    <div class="mt-5 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="data-table min-w-[76rem]">
                <thead><tr><th>Program & kegiatan</th><th>BUMDes</th><th>Tahun</th><th>Target</th><th>Periode</th><th>Capaian</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($plans as $plan)
                        @php($snapshot = $snapshots[$plan->id])
                        <tr>
                            <td><p class="font-semibold text-stone-950">{{ $plan->program }}</p><p class="mt-1 max-w-md text-xs leading-5 text-stone-500">{{ $plan->kegiatan }}</p></td>
                            <td>{{ $plan->bumdes->nama }}</td>
                            <td>{{ $plan->tahun }}</td>
                            <td>
                                @if ($plan->target_nilai !== null)
                                    <span class="font-semibold text-stone-900">{{ $plan->target_nilai }}</span> {{ $plan->target_satuan }}
                                @else
                                    <span class="text-xs leading-5">{{ $plan->target_deskripsi ?: 'Belum ditentukan' }}</span>
                                @endif
                            </td>
                            <td>{{ $plan->periode->label() }}<p class="mt-1 text-xs text-stone-500">{{ $snapshot['periode_terisi'] }}/{{ $plan->periode->maximumPeriods() }} terisi</p></td>
                            <td>
                                @if ($snapshot['persentase_capaian'] !== null)
                                    <span class="font-semibold text-stone-950">{{ $snapshot['persentase_capaian'] }}%</span>
                                    <div class="mt-2 h-1.5 w-24 overflow-hidden bg-stone-200" aria-hidden="true"><div class="h-full bg-emerald-700" style="width: {{ min(100, (float) $snapshot['persentase_capaian']) }}%"></div></div>
                                @else
                                    <span class="text-xs text-stone-500">Tidak terhitung</span>
                                @endif
                            </td>
                            <td><span class="{{ $snapshot['status']->badgeClass() }}">{{ $snapshot['status']->label() }}</span></td>
                            <td class="text-right"><a href="{{ route('bumdes.rencana-aksi.show', [$plan->bumdes, $plan]) }}" class="table-action">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-14 text-center text-sm text-stone-500">Belum ada rencana aksi untuk tahun {{ $year }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($plans->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $plans->links() }}</div>@endif
    </div>
@endsection
