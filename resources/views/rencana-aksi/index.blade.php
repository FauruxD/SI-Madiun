@extends('layouts.app', ['title' => 'Rencana Aksi & Realisasi'])

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">Kinerja BUMDes</p>
            <h1 class="page-title">Rencana aksi & realisasi</h1>
            <p class="page-lead">Input rencana kerja tahunan dan catat realisasinya per periode. Capaian serta status program dihitung otomatis.</p>
        </div>
    </div>

    <section class="mt-8 border border-stone-200 bg-white" aria-labelledby="plan-input-heading">
        <div class="border-b border-stone-200 p-5 sm:p-7">
            <p class="eyebrow">BUM-F-008</p>
            <h2 id="plan-input-heading" class="section-title">{{ $canCreatePlans ? '1. Pilih BUMDes untuk input rencana' : 'Pilih BUMDes untuk dipantau' }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-stone-600">
                {{ $canCreatePlans ? 'Setiap rencana disimpan untuk satu BUMDes/BUMDesma dan satu tahun pelaporan.' : 'Admin Kawasan dapat memantau rencana dan capaian BUMDes/BUMDesma dalam kawasan yang ditugaskan.' }}
            </p>

            @if ($availableBumdes->isNotEmpty())
                <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_10rem_auto]">
                    <div>
                        <label for="plan-bumdes" class="form-label">BUMDes/BUMDesma</label>
                        <select id="plan-bumdes" name="bumdes_id" class="form-input" required>
                            <option value="">Pilih BUMDes/BUMDesma</option>
                            @foreach ($availableBumdes as $bumdes)
                                <option value="{{ $bumdes->id }}" @selected($selectedBumdes?->id === $bumdes->id)>{{ $bumdes->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="plan-year" class="form-label">Tahun daftar</label>
                        <select id="plan-year" name="tahun" class="form-input">
                            @foreach ($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" @selected($year === (int) $availableYear)>{{ $availableYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn-primary self-end">Pilih BUMDes</button>
                </form>
            @else
                <div class="mt-5 border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950">
                    Belum ada BUMDes/BUMDesma yang berada dalam kewenangan akun ini.
                </div>
            @endif
        </div>

        @if ($selectedBumdes && $canCreatePlans)
            <div class="p-5 sm:p-7">
                <div class="flex flex-col gap-2 border-b border-stone-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-800">2. Input rencana kerja tahunan</p>
                        <h3 class="mt-2 text-xl font-semibold text-stone-950">{{ $selectedBumdes->nama }}</h3>
                    </div>
                    <p class="max-w-xl text-sm leading-6 text-stone-600">Isi program, kegiatan, target, dan periode pemantauan. Setelah disimpan, Anda langsung diarahkan ke input realisasi.</p>
                </div>

                <form method="POST" action="{{ route('bumdes.rencana-aksi.store', $selectedBumdes) }}" class="mt-6">
                    @csrf
                    @include('rencana-aksi._form', ['defaultYear' => $year])
                    <div class="mt-7 flex justify-end border-t border-stone-200 pt-5">
                        <button class="btn-primary">Simpan & lanjut input realisasi</button>
                    </div>
                </form>
            </div>
        @elseif ($selectedBumdes)
            <div class="m-5 border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-950 sm:m-7">
                <p class="font-bold">Mode pemantauan</p>
                <p class="mt-1">Rencana kerja dan realisasi diinput oleh Admin Desa atau Super Admin. Pilih <strong>Lihat realisasi</strong> pada daftar di bawah untuk memantau capaiannya.</p>
            </div>
        @elseif ($availableBumdes->isNotEmpty() && $canCreatePlans)
            <div class="p-5 text-sm leading-6 text-stone-600 sm:p-7">Pilih BUMDes/BUMDesma terlebih dahulu untuk membuka formulir rencana kerja.</div>
        @endif
    </section>

    <section class="mt-8" aria-labelledby="realization-list-heading">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">BUM-F-009 & BUM-F-010</p>
                <h2 id="realization-list-heading" class="section-title">{{ $canCreatePlans ? '3. Input realisasi berkala' : 'Rencana dan capaian' }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-stone-600">Buka rencana untuk mencatat realisasi bulanan, triwulanan, atau tahunan. Entri yang belum diisi berstatus Belum Mulai.</p>
            </div>
        </div>

        <form method="GET" class="mt-5 grid gap-3 border border-stone-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_10rem_auto]">
            @if ($selectedBumdes)
                <input type="hidden" name="bumdes_id" value="{{ $selectedBumdes->id }}">
            @endif
            <div>
                <label for="search" class="sr-only">Cari rencana aksi</label>
                <input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari BUMDes, program, atau kegiatan…">
            </div>
            <div>
                <label for="filter-tahun" class="sr-only">Filter tahun</label>
                <select id="filter-tahun" name="tahun" class="form-input mt-0">
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
                                <td class="text-right">
                                    <a href="{{ route('bumdes.rencana-aksi.show', [$plan->bumdes, $plan]) }}" class="table-action">
                                        @can('manageRealizations', $plan) Input realisasi @else Lihat realisasi @endcan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-14 text-center text-sm text-stone-500">
                                    @if ($selectedBumdes && $canCreatePlans)
                                        Belum ada rencana aksi {{ $selectedBumdes->nama }} untuk tahun {{ $year }}. Isi formulir di atas untuk mulai.
                                    @else
                                        Belum ada rencana aksi untuk tahun {{ $year }}.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($plans->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $plans->links() }}</div>@endif
        </div>
    </section>
@endsection
