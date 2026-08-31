@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <section class="flex flex-col gap-5 border-b border-stone-300 pb-8 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="eyebrow">Ringkasan ruang kerja</p>
            <h1 class="page-title">Selamat datang, {{ str(auth()->user()->name)->before(' ') }}.</h1>
            <p class="page-lead">Pantau data dan pekerjaan yang berada dalam kewenangan Anda.</p>
        </div>
        <div class="border-l-2 border-amber-400 pl-4 text-sm text-stone-600">
            <p class="font-semibold text-stone-900">{{ auth()->user()->role->label() }}</p>
            <p>{{ auth()->user()->role->shortLabel() }}</p>
        </div>
    </section>

    <section class="mt-8 grid gap-px overflow-hidden border border-stone-200 bg-stone-200 sm:grid-cols-2 xl:grid-cols-4" aria-label="Metrik utama">
        @foreach ($metrics as $metric)
            <article class="bg-white p-6 lg:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-4 font-display text-4xl text-emerald-950">{{ $metric['value'] }}</p>
                <p class="mt-2 text-sm text-stone-500">{{ $metric['meta'] }}</p>
            </article>
        @endforeach
    </section>

    @if (auth()->user()->role === \App\UserRole::AdminDesa)
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

    @if (auth()->user()->isSuperAdmin())
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
                <p class="eyebrow text-amber-900">Langkah berikutnya</p>
                <h2 class="mt-3 font-display text-3xl">Fondasi siap untuk data kawasan.</h2>
                <p class="mt-4 text-sm leading-6 text-amber-950/75">Phase 2 akan menambahkan tipologi dan entitas kawasan tanpa mengubah struktur akun atau master wilayah ini.</p>
            </aside>
        </section>
    @endif
@endsection
