@extends('layouts.app', ['title' => 'Kawasan Perdesaan'])

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">Manajemen kawasan</p>
            <h1 class="page-title">Kawasan perdesaan</h1>
            <p class="page-lead">Kelola identitas kawasan, desa anggota, tipologi, admin, dan dasar hukumnya.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.kategori-kawasan.index') }}" class="btn-secondary">Master kategori</a>
            <a href="{{ route('admin.kawasan.create') }}" class="btn-primary">Tambah kawasan</a>
        </div>
    </div>

    <form method="GET" class="mt-8 grid gap-3 border border-stone-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_13rem_12rem_auto]">
        <div><label for="search" class="sr-only">Cari kawasan</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari nama kawasan…"></div>
        <div><label for="category" class="sr-only">Filter kategori</label><select id="category" name="category" class="form-input mt-0"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->nama }}</option>@endforeach</select></div>
        <div><label for="status" class="sr-only">Filter status</label><select id="status" name="status" class="form-input mt-0"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
        <button class="btn-secondary">Terapkan</button>
    </form>

    <div class="mt-5 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="data-table min-w-[64rem]">
                <thead><tr><th>Kawasan</th><th>Kategori / tipologi</th><th>Desa</th><th>Admin Kawasan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($kawasans as $kawasan)
                        <tr>
                            <td><a href="{{ route('admin.kawasan.show', $kawasan) }}" class="font-semibold text-stone-950 underline-offset-4 hover:text-emerald-800 hover:underline">{{ $kawasan->nama }}</a><p class="mt-1 text-xs text-stone-500">Diperbarui {{ $kawasan->updated_at->diffForHumans() }}</p></td>
                            <td><div class="flex max-w-sm flex-wrap gap-1.5">@forelse ($kawasan->categories as $category)<span class="badge-neutral">{{ $category->nama }}</span>@empty<span class="text-stone-400">—</span>@endforelse</div></td>
                            <td><span class="font-semibold text-stone-950">{{ $kawasan->desas_count }}</span> desa</td>
                            <td>{{ $kawasan->adminUsers->pluck('name')->join(', ') ?: 'Belum ditugaskan' }}</td>
                            <td><span @class(['badge-success' => $kawasan->status === \App\Enums\KawasanStatus::Ditetapkan, 'badge-neutral' => $kawasan->status === \App\Enums\KawasanStatus::Draft])>{{ $kawasan->status->label() }}</span></td>
                            <td class="text-right"><div class="inline-flex gap-3"><a href="{{ route('admin.kawasan.show', $kawasan) }}" class="table-action">Detail</a><a href="{{ route('admin.kawasan.edit', $kawasan) }}" class="table-action">Ubah</a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-14 text-center text-sm text-stone-500">Belum ada kawasan yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kawasans->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $kawasans->links() }}</div>@endif
    </div>
@endsection
