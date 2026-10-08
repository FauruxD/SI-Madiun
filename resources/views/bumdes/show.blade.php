@extends('layouts.app', ['title' => $bumdes->nama])

@section('content')
    <div class="flex flex-col gap-6 border-b border-stone-300 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div><a href="{{ route('bumdes.index') }}" class="back-link">← Daftar BUMDes</a><p class="eyebrow mt-6">{{ $bumdes->jenis->label() }}</p><h1 class="page-title">{{ $bumdes->nama }}</h1><div class="mt-4 flex flex-wrap gap-2"><span class="badge-neutral">{{ $bumdes->status_hukum ?: 'Status hukum belum diisi' }}</span>@foreach ($bumdes->desas as $desa)<span @class(['badge-success' => $desa->pivot->is_desa_utama, 'badge-neutral' => ! $desa->pivot->is_desa_utama])>{{ $desa->nama }}{{ $desa->pivot->is_desa_utama ? ' · utama' : '' }}</span>@endforeach</div></div>
        <div class="flex flex-wrap gap-3">@can('create', [\App\Models\RencanaAksi::class, $bumdes])<a href="{{ route('bumdes.rencana-aksi.create', $bumdes) }}" class="btn-primary">Tambah rencana aksi</a>@endcan @can('update', $bumdes)<a href="{{ route('bumdes.edit', $bumdes) }}" class="btn-secondary">Ubah profil</a>@endcan @can('delete', $bumdes)<form action="{{ route('bumdes.destroy', $bumdes) }}" method="POST" onsubmit="return confirm('Arsipkan BUMDes ini?')">@csrf @method('DELETE')<button class="btn-secondary text-red-700">Arsipkan</button></form>@endcan</div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">@can('manageWebsite', $bumdes)<a href="{{ route('bumdes.website.edit', $bumdes) }}" class="btn-secondary">Kelola website</a>@endcan @can('downloadCompanyProfile', $bumdes)<a href="{{ route('bumdes.company-profile.download', $bumdes) }}" class="btn-secondary">Unduh Company Profile</a>@endcan</div>

    @php($tabs = ['profil' => 'Profil', 'organisasi' => 'Organisasi', 'unit-usaha' => 'Unit usaha', 'produk' => 'Produk & layanan', 'aset' => 'Aset', 'keuangan' => 'Keuangan', 'kemitraan' => 'Kemitraan', 'galeri' => 'Galeri', 'dokumen' => 'Dokumen'])
    <nav class="mt-7 flex gap-1 overflow-x-auto border-b border-stone-200" aria-label="Bagian data BUMDes">@foreach ($tabs as $key => $label)<a href="{{ route('bumdes.show', ['bumdes' => $bumdes, 'tab' => $key]) }}" @class(['tab-link', 'tab-link-active' => $tab === $key]) @if($tab === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>

    <div class="mt-6">@include('bumdes.tabs.'.$tab)</div>
@endsection
