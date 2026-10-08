@extends('layouts.app', ['title' => 'Potensi Desa'])

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">Katalog & investasi</p>
            <h1 class="page-title">Potensi desa</h1>
            <p class="page-lead">Kelola profil potensi, keterhubungan BUMDes, media, dan kesiapan publikasinya.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.kategori-potensi.index') }}" class="btn-secondary">Kategori</a>
            @endif
            @can('create', \App\Models\PotensiDesa::class)
                <a href="{{ route('potensi-desa.create') }}" class="btn-primary">Tambah potensi</a>
            @endcan
        </div>
    </div>

    <form method="GET" class="mt-8 grid gap-3 border border-stone-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_13rem_11rem_auto]">
        <div><label for="search" class="sr-only">Cari potensi</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari judul atau desa…"></div>
        <div>
            <label for="kategori" class="sr-only">Filter kategori</label>
            <select id="kategori" name="kategori" class="form-input mt-0">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('kategori') === $category->id)>{{ $category->nama }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="status" class="sr-only">Filter status</label>
            <select id="status" name="status" class="form-input mt-0">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach
            </select>
        </div>
        <button class="btn-secondary">Terapkan</button>
    </form>

    <div class="mt-5 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="data-table min-w-[72rem]">
                <thead><tr><th>Judul</th><th>Desa</th><th>Kategori</th><th>BUMDes</th><th>Status</th><th>Diperbarui</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($potentials as $potential)
                        <tr>
                            <td><p class="font-semibold text-stone-950">{{ $potential->judul }}</p><p class="mt-1 max-w-sm text-xs leading-5 text-stone-500">{{ str($potential->deskripsi)->limit(90) }}</p></td>
                            <td><span class="font-medium text-stone-900">{{ $potential->village->nama }}</span><p class="mt-1 text-xs text-stone-500">{{ $potential->village->kecamatan->nama }}</p></td>
                            <td>{{ $potential->category->nama }}</td>
                            <td>{{ $potential->bumdes?->nama ?? '—' }}</td>
                            <td><span class="{{ $potential->status->badgeClass() }}">{{ $potential->status->label() }}</span></td>
                            <td>{{ $potential->updated_at->translatedFormat('d M Y') }}</td>
                            <td class="text-right"><a href="{{ route('potensi-desa.show', $potential) }}" class="table-action">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-14 text-center text-sm text-stone-500">Belum ada potensi yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($potentials->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $potentials->links() }}</div>@endif
    </div>
@endsection
