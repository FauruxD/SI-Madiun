@extends('layouts.app', ['title' => 'Master Desa'])

@section('content')
    <div class="section-heading"><div><p class="eyebrow">Referensi administratif</p><h1 class="page-title">Master wilayah</h1><p class="page-lead">Struktur berjenjang Kabupaten → Kecamatan → Desa.</p></div><a href="{{ route('admin.desa.create') }}" class="btn-primary">Tambah desa</a></div>
    @include('admin.wilayah._tabs')
    <div class="mt-5 overflow-hidden border border-stone-200 bg-white"><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Kode</th><th>Nama desa</th><th>Kecamatan</th><th>Admin</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse ($desas as $desa)<tr><td class="font-mono text-xs">{{ $desa->kode }}</td><td class="font-semibold text-stone-950">{{ $desa->nama }}</td><td>{{ $desa->kecamatan->nama }}</td><td>{{ $desa->users_count }}</td><td class="text-right"><div class="inline-flex items-center gap-3"><a href="{{ route('admin.desa.edit', $desa) }}" class="table-action">Ubah</a><form action="{{ route('admin.desa.destroy', $desa) }}" method="POST" onsubmit="return confirm('Hapus desa ini?')">@csrf @method('DELETE')<button class="table-action-danger">Hapus</button></form></div></td></tr>
        @empty <tr><td colspan="5" class="py-12 text-center text-sm text-stone-500">Belum ada data desa.</td></tr>@endforelse
    </tbody></table></div>@if($desas->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $desas->links() }}</div>@endif</div>
@endsection
