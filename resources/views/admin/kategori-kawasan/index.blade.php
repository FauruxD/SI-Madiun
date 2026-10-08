@extends('layouts.app', ['title' => 'Kategori Kawasan'])

@section('content')
    <div class="section-heading"><div><a href="{{ route('admin.kawasan.index') }}" class="back-link">← Kawasan perdesaan</a><p class="eyebrow mt-6">Master data</p><h1 class="page-title">Kategori kawasan</h1><p class="page-lead">Tipologi bersifat relasional dan dapat dipakai oleh banyak kawasan.</p></div><a href="{{ route('admin.kategori-kawasan.create') }}" class="btn-primary">Tambah kategori</a></div>
    <form method="GET" class="mt-8 flex gap-3 border border-stone-200 bg-white p-4"><div class="min-w-0 flex-1"><label for="search" class="sr-only">Cari kategori</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari nama atau kode…"></div><button class="btn-secondary">Cari</button></form>
    <div class="mt-5 overflow-hidden border border-stone-200 bg-white"><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Dipakai</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse ($categories as $category)<tr><td class="font-mono text-xs font-semibold">{{ $category->kode }}</td><td class="font-semibold text-stone-950">{{ $category->nama }}</td><td class="max-w-md">{{ str($category->deskripsi ?: '—')->limit(90) }}</td><td>{{ $category->kawasans_count }} kawasan</td><td><span @class(['badge-success' => $category->is_active, 'badge-neutral' => ! $category->is_active])>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="text-right"><a href="{{ route('admin.kategori-kawasan.edit', $category) }}" class="table-action">Ubah</a></td></tr>
        @empty<tr><td colspan="6" class="py-12 text-center text-sm text-stone-500">Belum ada kategori yang cocok.</td></tr>@endforelse
    </tbody></table></div>@if ($categories->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $categories->links() }}</div>@endif</div>
@endsection
