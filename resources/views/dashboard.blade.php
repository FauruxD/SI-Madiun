@extends('layouts.app', ['title' => 'Dashboard'])

@php
    $role = auth()->user()->role;
    $pageHeading = match ($role) {
        \App\UserRole::SuperAdmin => 'Ringkasan Kabupaten Madiun',
        \App\UserRole::AdminKawasan => 'Ringkasan kawasan',
        \App\UserRole::AdminDesa => 'Ringkasan desa & BUMDes',
    };
    $pageDescription = match ($role) {
        \App\UserRole::SuperAdmin => 'Pantau kondisi kawasan, kelembagaan ekonomi desa, publikasi potensi, dan capaian rencana aksi dalam satu pandangan.',
        \App\UserRole::AdminKawasan => 'Pantau desa dan BUMDes anggota, antrean verifikasi proposal, peta, serta capaian kawasan yang ditugaskan.',
        \App\UserRole::AdminDesa => 'Pantau kelengkapan profil BUMDes, produk, rencana aksi, realisasi, serta status E-Proposal dalam desa tugas.',
    };
@endphp

@section('content')
    <section class="flex flex-col gap-6 border-b border-stone-300 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <p class="eyebrow">Dashboard {{ auth()->user()->role->label() }}</p>
            <h1 class="page-title">{{ $pageHeading }}</h1>
            <p class="page-lead">{{ $pageDescription }}</p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex shrink-0 items-end gap-3" aria-label="Pilih tahun pelaporan">
            <div>
                <label for="dashboard-year" class="form-label">Tahun pelaporan</label>
                <select id="dashboard-year" name="tahun" class="form-input min-w-32">
                    @foreach (range(now()->year + 1, now()->year - 4) as $optionYear)
                        <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-secondary">Tampilkan</button>
        </form>
    </section>

    <section @class([
        'mt-8 grid gap-px overflow-hidden border border-stone-200 bg-stone-200 sm:grid-cols-2',
        'xl:grid-cols-6' => count($metrics) > 4,
        'xl:grid-cols-4' => count($metrics) <= 4,
    ]) aria-label="Metrik utama">
        @foreach ($metrics as $metric)
            <article class="min-w-0 bg-white p-5 lg:p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-4 break-words font-display text-3xl leading-none text-emerald-950 lg:text-4xl">{{ $metric['value'] }}</p>
                <p class="mt-2 text-sm leading-5 text-stone-500">{{ $metric['meta'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-8 grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,.75fr)]" aria-label="Status kinerja dan proposal">
        <article class="min-w-0 border border-stone-200 bg-emerald-950 p-6 text-white sm:p-7">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-200">Capaian rencana aksi · {{ $year }}</p>
                    <p class="mt-3 font-display text-5xl text-white">
                        {{ $achievement['rata_rata_capaian'] === null ? '—' : number_format((float) $achievement['rata_rata_capaian'], 2, ',', '.').'%' }}
                    </p>
                    <p class="mt-2 text-sm text-emerald-100/70">{{ $achievement['jumlah_valid'] }} dari {{ $achievement['jumlah_rencana'] }} rencana memiliki capaian numerik.</p>
                </div>
                <a href="{{ route('rencana-aksi.index', ['tahun' => $year]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-amber-300 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-300">Buka rencana & realisasi →</a>
            </div>
            <dl class="mt-7 grid grid-cols-2 gap-px bg-white/15 sm:grid-cols-4">
                <div class="bg-emerald-950 px-4 py-3"><dt class="text-xs text-emerald-100/65">Belum mulai</dt><dd class="mt-1 text-xl font-semibold">{{ $achievement['jumlah_belum_mulai'] }}</dd></div>
                <div class="bg-emerald-950 px-4 py-3"><dt class="text-xs text-emerald-100/65">Berjalan</dt><dd class="mt-1 text-xl font-semibold">{{ $achievement['jumlah_berjalan'] }}</dd></div>
                <div class="bg-emerald-950 px-4 py-3"><dt class="text-xs text-emerald-100/65">Selesai</dt><dd class="mt-1 text-xl font-semibold">{{ $achievement['jumlah_selesai'] }}</dd></div>
                <div class="bg-emerald-950 px-4 py-3"><dt class="text-xs text-emerald-100/65">Tertunda</dt><dd class="mt-1 text-xl font-semibold">{{ $achievement['jumlah_tertunda'] }}</dd></div>
            </dl>
        </article>

        <article class="border border-stone-200 bg-white p-6 sm:p-7">
            <p class="eyebrow">Alur E-Proposal</p>
            <h2 class="mt-2 font-display text-2xl text-stone-950">Status proposal</h2>
            <dl class="mt-5 divide-y divide-stone-100">
                @foreach (\App\Enums\EProposalStatus::cases() as $status)
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <dt><span class="{{ $status->badgeClass() }}">{{ $status->label() }}</span></dt>
                        <dd class="font-display text-2xl text-stone-950">{{ $proposalStatusCounts[$status->value] }}</dd>
                    </div>
                @endforeach
            </dl>
            <a href="{{ $role === \App\UserRole::AdminDesa ? route('admin.eproposal.index') : route('admin.eproposal.review.index') }}" class="mt-6 inline-flex min-h-11 items-center text-sm font-bold text-emerald-800 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800">{{ $role === \App\UserRole::AdminDesa ? 'Kelola E-Proposal' : 'Buka verifikasi proposal' }} →</a>
        </article>
    </section>

    @if ($role === \App\UserRole::AdminDesa)
        <section class="mt-10">
            <div class="section-heading">
                <div><p class="eyebrow">Kelembagaan desa</p><h2 class="section-title">Profil dan kinerja BUMDes</h2></div>
                <a href="{{ route('bumdes.index') }}" class="back-link">Lihat seluruh BUMDes →</a>
            </div>
            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                @forelse ($bumdesSummaries as $summary)
                    @php($bumdes = $summary['model'])
                    <article class="min-w-0 border border-stone-200 bg-white p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-800">{{ $bumdes->jenis->label() }}</p>
                                <h3 class="mt-2 break-words font-display text-2xl text-stone-950">{{ $bumdes->nama }}</h3>
                            </div>
                            <a href="{{ route('bumdes.show', $bumdes) }}" class="back-link">Rincian →</a>
                        </div>

                        <div class="mt-6 border-t border-stone-100 pt-5">
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <p class="font-semibold text-stone-800">Kelengkapan profil operasional</p>
                                <p class="text-stone-500">{{ $summary['profile_completed'] }} dari {{ $summary['profile_total'] }} data inti terisi</p>
                            </div>
                            <div class="mt-3 grid grid-cols-7 gap-1" role="img" aria-label="{{ $summary['profile_completed'] }} dari {{ $summary['profile_total'] }} data inti profil terisi">
                                @for ($field = 1; $field <= $summary['profile_total']; $field++)
                                    <span @class(['h-2', 'bg-emerald-700' => $field <= $summary['profile_completed'], 'bg-stone-200' => $field > $summary['profile_completed']]) aria-hidden="true"></span>
                                @endfor
                            </div>
                            <p class="mt-2 text-xs leading-5 text-stone-500">Indikator ini menghitung isian registrasi, tahun berdiri, status hukum, alamat, telepon, email, dan deskripsi—bukan skor penilaian resmi.</p>
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-px bg-stone-200 sm:grid-cols-4">
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Unit usaha</dt><dd class="mt-1 text-lg font-bold">{{ $summary['business_unit_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Produk aktif</dt><dd class="mt-1 text-lg font-bold">{{ $summary['active_product_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Rencana {{ $year }}</dt><dd class="mt-1 text-lg font-bold">{{ $summary['action_plan_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Rata-rata capaian</dt><dd class="mt-1 text-lg font-bold">{{ $summary['achievement']['rata_rata_capaian'] === null ? '—' : number_format((float) $summary['achievement']['rata_rata_capaian'], 2, ',', '.').'%' }}</dd></div>
                        </dl>
                    </article>
                @empty
                    <div class="border border-dashed border-stone-300 bg-stone-50 p-7 text-sm leading-6 text-stone-600 lg:col-span-2">Belum ada BUMDes atau BUMDesma yang terhubung ke desa tugas Anda.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-10">
            <div class="section-heading"><div><p class="eyebrow">Cakupan akses</p><h2 class="section-title">Desa yang ditugaskan</h2></div></div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($assignedDesas as $desa)
                    <a href="{{ route('desa.show', $desa) }}" class="group border border-stone-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 motion-reduce:transform-none">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-800">{{ $desa->kode }}</p>
                        <h3 class="mt-3 font-display text-2xl">{{ $desa->nama }}</h3>
                        <p class="mt-2 text-sm text-stone-500">{{ $desa->kecamatan->nama }} · {{ $desa->kecamatan->kabupaten->nama }}</p>
                        <span class="mt-5 inline-block text-sm font-semibold text-emerald-800 group-hover:underline">Lihat rincian →</span>
                    </a>
                @empty
                    <div class="border border-dashed border-stone-300 bg-stone-50 p-7 text-sm text-stone-600">Belum ada desa yang ditugaskan. Hubungi Super Admin.</div>
                @endforelse
            </div>
        </section>
    @endif

    @if ($role === \App\UserRole::AdminKawasan)
        <section class="mt-10">
            <div class="section-heading">
                <div><p class="eyebrow">Rekap wilayah tugas</p><h2 class="section-title">Kawasan, desa, dan BUMDes</h2></div>
                <a href="{{ route('admin.eproposal.review.index') }}" class="back-link">Tinjau proposal →</a>
            </div>
            <div class="mt-5 grid gap-5 xl:grid-cols-2">
                @forelse ($kawasanSummaries as $summary)
                    @php($kawasan = $summary['model'])
                    <article class="min-w-0 border border-stone-200 bg-white p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-800">{{ $kawasan->status->label() }}</p>
                                <h3 class="mt-2 break-words font-display text-2xl">{{ $kawasan->nama }}</h3>
                                <p class="mt-2 text-sm leading-6 text-stone-500">{{ $kawasan->categories->pluck('nama')->join(' · ') ?: 'Kategori belum ditetapkan' }}</p>
                            </div>
                            <a href="{{ route('kawasan.show', $kawasan) }}" class="back-link">Rincian →</a>
                        </div>
                        <dl class="mt-5 grid grid-cols-2 gap-px bg-stone-200 sm:grid-cols-4">
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Desa</dt><dd class="mt-1 text-lg font-bold">{{ $summary['village_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">BUMDes</dt><dd class="mt-1 text-lg font-bold">{{ $summary['bumdes_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Proposal</dt><dd class="mt-1 text-lg font-bold">{{ $summary['proposal_count'] }}</dd></div>
                            <div class="bg-stone-50 p-3"><dt class="text-xs text-stone-500">Capaian</dt><dd class="mt-1 text-lg font-bold">{{ $summary['achievement']['rata_rata_capaian'] === null ? '—' : number_format((float) $summary['achievement']['rata_rata_capaian'], 2, ',', '.').'%' }}</dd></div>
                        </dl>
                        <div class="mt-5">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">BUMDes/BUMDesma anggota</p>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @forelse ($summary['bumdes'] as $bumdes)
                                    <li><a href="{{ route('bumdes.show', $bumdes) }}" class="inline-flex min-h-9 items-center border border-stone-200 bg-stone-50 px-3 text-xs font-semibold text-stone-700 hover:border-emerald-700 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800">{{ $bumdes->nama }}</a></li>
                                @empty
                                    <li class="text-sm text-stone-500">Belum ada BUMDes yang terhubung.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-2 border-t border-stone-100 pt-4" aria-label="Status proposal {{ $kawasan->nama }}">
                            @foreach (\App\Enums\EProposalStatus::cases() as $status)
                                <span class="{{ $status->badgeClass() }}">{{ $status->label() }} · {{ $summary['proposal_counts'][$status->value] }}</span>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-stone-300 bg-stone-50 p-7 text-sm leading-6 text-stone-600 xl:col-span-2">Belum ada kawasan yang ditugaskan. Hubungi Super Admin.</div>
                @endforelse
            </div>
        </section>

        <section class="mt-10">
            <div class="section-heading"><div><p class="eyebrow">Konteks spasial</p><h2 class="section-title">Peta kawasan tugas</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">Layer peta mengikuti kewenangan akun Anda dan tetap menyediakan daftar lokasi saat peta dasar tidak tersedia.</p></div><a href="{{ route('peta.manage') }}" class="back-link">Kelola geospasial →</a></div>
            <div class="mt-5">@include('maps.partials.explorer', ['collection' => $mapCollection])</div>
        </section>
    @endif

    @if ($role === \App\UserRole::SuperAdmin)
        <section class="mt-10">
            <div class="section-heading">
                <div><p class="eyebrow">Rekap lintas kawasan</p><h2 class="section-title">Kinerja per kawasan</h2></div>
                <a href="{{ route('admin.kawasan.index') }}" class="back-link">Kelola kawasan →</a>
            </div>
            <div class="mt-5 overflow-x-auto border border-stone-200 bg-white">
                <table class="data-table">
                    <thead><tr><th>Kawasan</th><th>Desa</th><th>BUMDes</th><th>Proposal</th><th>Menunggu</th><th>Capaian {{ $year }}</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($kawasanSummaries as $summary)
                            @php($kawasan = $summary['model'])
                            <tr>
                                <td><p class="font-semibold text-stone-950">{{ $kawasan->nama }}</p><p class="mt-1 text-xs text-stone-500">{{ $kawasan->status->label() }}</p></td>
                                <td>{{ $summary['village_count'] }}</td>
                                <td>{{ $summary['bumdes_count'] }}</td>
                                <td>{{ $summary['proposal_count'] }}</td>
                                <td>{{ $summary['proposal_counts'][\App\Enums\EProposalStatus::Diajukan->value] }}</td>
                                <td>{{ $summary['achievement']['rata_rata_capaian'] === null ? '—' : number_format((float) $summary['achievement']['rata_rata_capaian'], 2, ',', '.').'%' }}</td>
                                <td class="text-right"><a href="{{ route('admin.kawasan.show', $kawasan) }}" class="table-action">Rincian →</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-10 text-center text-stone-500">Belum ada kawasan yang dapat direkap.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-10 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,.65fr)]">
            <div class="border border-stone-200 bg-white">
                <div class="section-heading border-b border-stone-200 px-6 py-5"><div><p class="eyebrow">Audit sistem</p><h2 class="section-title">Aktivitas terbaru</h2></div></div>
                <div class="divide-y divide-stone-100">
                    @forelse ($recentActivities as $activity)
                        <div class="flex items-start justify-between gap-5 px-6 py-4">
                            <div><p class="text-sm font-semibold">{{ str($activity->action)->replace('.', ' · ')->headline() }}</p><p class="mt-1 text-xs text-stone-500">{{ $activity->user?->name ?? 'Sistem' }}</p></div>
                            <time class="shrink-0 text-xs text-stone-500" datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-stone-500">Belum ada aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>
            <aside class="bg-amber-100 p-7">
                <p class="eyebrow text-amber-900">Fokus operasional</p>
                <h2 class="mt-3 font-display text-3xl">Data siap ditindaklanjuti.</h2>
                <p class="mt-4 text-sm leading-6 text-amber-950/75">Gunakan rekap di atas untuk memprioritaskan kawasan dengan proposal yang menunggu verifikasi atau capaian rencana aksi yang belum terisi.</p>
                <a href="{{ route('admin.eproposal.review.index') }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-bold text-amber-950 underline underline-offset-4">Buka verifikasi proposal →</a>
            </aside>
        </section>
    @endif
@endsection
