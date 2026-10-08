@extends('layouts.app', ['title' => 'BUMDes & BUMDesma'])

@section('content')
    <div class="section-heading">
        <div>
            <p class="eyebrow">Basis data usaha desa</p>
            <h1 class="page-title">BUMDes & BUMDesma</h1>
            <p class="page-lead">Profil, legalitas, organisasi, unit usaha, aset, keuangan, dan kemitraan dalam satu ruang kerja.</p>
        </div>
        @can('create', \App\Models\Bumdes::class)
            <a href="{{ route('bumdes.create') }}" class="btn-primary">Tambah BUMDes</a>
        @endcan
    </div>

    <form method="GET" class="mt-8 grid gap-3 border border-stone-200 bg-white p-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_10rem_13rem_13rem_13rem_auto]">
        <div><label for="search" class="sr-only">Cari BUMDes</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Nama atau nomor registrasi…"></div>
        <div><label for="jenis" class="sr-only">Filter jenis</label><select id="jenis" name="jenis" class="form-input mt-0"><option value="">Semua jenis</option>@foreach ($jenisOptions as $jenis)<option value="{{ $jenis->value }}" @selected(request('jenis') === $jenis->value)>{{ $jenis->label() }}</option>@endforeach</select></div>
        <div><label for="desa" class="sr-only">Filter desa</label><select id="desa" name="desa" class="form-input mt-0"><option value="">Semua desa</option>@foreach ($desas as $desa)<option value="{{ $desa->id }}" @selected((int) request('desa') === $desa->id)>{{ $desa->nama }}</option>@endforeach</select></div>
        <div><label for="kecamatan" class="sr-only">Filter kecamatan</label><select id="kecamatan" name="kecamatan" class="form-input mt-0"><option value="">Semua kecamatan</option>@foreach ($kecamatans as $kecamatan)<option value="{{ $kecamatan->id }}" @selected((int) request('kecamatan') === $kecamatan->id)>{{ $kecamatan->nama }}</option>@endforeach</select></div>
        <div><label for="status" class="sr-only">Filter status hukum</label><select id="status" name="status" class="form-input mt-0"><option value="">Semua status hukum</option>@foreach ($statusHukumOptions as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <button class="btn-secondary">Terapkan</button>
    </form>

    <div class="mt-5 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="data-table min-w-[74rem]">
                <thead><tr><th>Nama</th><th>Jenis</th><th>Desa</th><th>Kecamatan</th><th>Nomor registrasi</th><th>Status hukum</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($bumdesList as $bumdes)
                        <tr>
                            <td><a href="{{ route('bumdes.show', $bumdes) }}" class="font-semibold text-stone-950 underline-offset-4 hover:text-emerald-800 hover:underline">{{ $bumdes->nama }}</a><p class="mt-1 text-xs text-stone-500">Diperbarui {{ $bumdes->updated_at->diffForHumans() }}</p></td>
                            <td><span class="badge-neutral">{{ $bumdes->jenis->label() }}</span></td>
                            <td>{{ $bumdes->desas->pluck('nama')->join(', ') ?: '—' }}</td>
                            <td>{{ $bumdes->desas->pluck('kecamatan.nama')->unique()->join(', ') ?: '—' }}</td>
                            <td class="font-mono text-xs">{{ $bumdes->nomor_registrasi ?: '—' }}</td>
                            <td>{{ $bumdes->status_hukum ?: 'Belum diisi' }}</td>
                            <td class="text-right"><div class="inline-flex gap-3"><a href="{{ route('bumdes.show', $bumdes) }}" class="table-action">Detail</a>@can('update', $bumdes)<a href="{{ route('bumdes.edit', $bumdes) }}" class="table-action">Ubah</a>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-14 text-center text-sm text-stone-500">Belum ada BUMDes yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bumdesList->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $bumdesList->links() }}</div>@endif
    </div>
@endsection
