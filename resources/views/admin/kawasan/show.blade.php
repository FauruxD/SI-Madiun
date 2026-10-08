@extends('layouts.app', ['title' => $kawasan->nama])

@section('content')
    <div class="flex flex-col gap-6 border-b border-stone-300 pb-8 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.kawasan.index') : route('dashboard') }}" class="back-link">← {{ auth()->user()->isSuperAdmin() ? 'Daftar kawasan' : 'Dashboard' }}</a>
            <p class="eyebrow mt-6">Kawasan perdesaan</p>
            <h1 class="page-title">{{ $kawasan->nama }}</h1>
            <div class="mt-4 flex flex-wrap items-center gap-2"><span @class(['badge-success' => $kawasan->status === \App\Enums\KawasanStatus::Ditetapkan, 'badge-neutral' => $kawasan->status === \App\Enums\KawasanStatus::Draft])>{{ $kawasan->status->label() }}</span>@foreach ($kawasan->categories as $category)<span class="badge-neutral">{{ $category->nama }}</span>@endforeach</div>
        </div>
        @if (auth()->user()->isSuperAdmin())
            <div class="flex flex-wrap gap-3"><a href="{{ route('admin.kawasan.edit', $kawasan) }}" class="btn-primary">Ubah kawasan</a><form action="{{ route('admin.kawasan.destroy', $kawasan) }}" method="POST" onsubmit="return confirm('Hapus kawasan ini? Data akan disimpan sebagai arsip dan tidak tampil di daftar aktif.')">@csrf @method('DELETE')<button class="btn-secondary text-red-700">Hapus</button></form></div>
        @endif
    </div>

    <section class="mt-8 grid gap-px overflow-hidden border border-stone-200 bg-stone-200 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan kawasan">
        <article class="bg-white p-6"><p class="eyebrow">Desa anggota</p><p class="mt-3 font-display text-4xl">{{ $kawasan->desas->count() }}</p></article>
        <article class="bg-white p-6"><p class="eyebrow">Kategori</p><p class="mt-3 font-display text-4xl">{{ $kawasan->categories->count() }}</p></article>
        <article class="bg-white p-6"><p class="eyebrow">Admin Kawasan</p><p class="mt-3 font-display text-4xl">{{ $kawasan->adminUsers->count() }}</p></article>
        <article class="bg-white p-6"><p class="eyebrow">Dokumen hukum</p><p class="mt-3 font-display text-4xl">{{ $kawasan->documents->count() }}</p></article>
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(18rem,.75fr)]">
        <section class="border border-stone-200 bg-white p-6 sm:p-7">
            <p class="eyebrow">Informasi dasar</p><h2 class="section-title">Profil kawasan</h2>
            <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Deskripsi</dt><dd class="mt-2 whitespace-pre-line text-sm leading-7 text-stone-700">{{ $kawasan->deskripsi ?: 'Belum ada deskripsi.' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Tanggal penetapan</dt><dd class="mt-2 text-sm font-semibold text-stone-900">{{ $kawasan->tanggal_penetapan?->translatedFormat('d F Y') ?? 'Belum tersedia' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-[0.12em] text-stone-500">Terakhir diperbarui</dt><dd class="mt-2 text-sm font-semibold text-stone-900">{{ $kawasan->updated_at->translatedFormat('d F Y, H:i') }}</dd></div>
            </dl>
        </section>
        <section class="border border-stone-200 bg-white p-6 sm:p-7">
            <p class="eyebrow">Penugasan</p><h2 class="section-title">Admin Kawasan</h2>
            <div class="mt-5 space-y-3">@forelse ($kawasan->adminUsers as $admin)<div class="border-l-2 border-emerald-700 pl-4"><p class="font-semibold text-stone-950">{{ $admin->name }}</p><p class="mt-1 text-xs text-stone-500">{{ $admin->email }} · {{ $admin->is_active ? 'aktif' : 'nonaktif' }}</p></div>@empty<p class="text-sm text-stone-500">Belum ada Admin Kawasan yang ditugaskan.</p>@endforelse</div>
        </section>
    </div>

    <section class="mt-6 border border-stone-200 bg-white">
        <div class="section-heading border-b border-stone-200 px-6 py-5"><div><p class="eyebrow">Cakupan wilayah</p><h2 class="section-title">Desa anggota</h2></div></div>
        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Desa</th><th>Kode</th><th>Kecamatan</th><th>Kabupaten</th></tr></thead><tbody>@forelse ($kawasan->desas->sortBy([['nama', 'asc'], ['id', 'asc']]) as $desa)<tr><td class="font-semibold text-stone-950">{{ $desa->nama }}</td><td class="font-mono text-xs">{{ $desa->kode }}</td><td>{{ $desa->kecamatan->nama }}</td><td>{{ $desa->kecamatan->kabupaten->nama }}</td></tr>@empty<tr><td colspan="4" class="py-10 text-center text-sm text-stone-500">Belum ada desa anggota.</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="mt-6 border border-stone-200 bg-white">
        <div class="section-heading border-b border-stone-200 px-6 py-5"><div><p class="eyebrow">Dasar hukum</p><h2 class="section-title">Dokumen legalitas</h2></div></div>
        @can('manageDocuments', $kawasan)
            <form action="{{ route('admin.kawasan.dokumen.store', $kawasan) }}" method="POST" enctype="multipart/form-data" class="grid gap-4 border-b border-stone-200 bg-stone-50 p-5 sm:grid-cols-2 xl:grid-cols-4">@csrf
                <div><label for="jenis_dokumen" class="form-label">Jenis dokumen</label><select id="jenis_dokumen" name="jenis_dokumen" required class="form-input">@foreach (\App\Enums\JenisDokumenKawasan::cases() as $type)<option value="{{ $type->value }}" @selected(old('jenis_dokumen') === $type->value)>{{ $type->label() }}</option>@endforeach</select><x-input-error name="jenis_dokumen" /></div>
                <div><label for="nomor_dokumen" class="form-label">Nomor dokumen</label><input id="nomor_dokumen" name="nomor_dokumen" value="{{ old('nomor_dokumen') }}" class="form-input" placeholder="Opsional"><x-input-error name="nomor_dokumen" /></div>
                <div><label for="tanggal_dokumen" class="form-label">Tanggal dokumen</label><input id="tanggal_dokumen" type="date" name="tanggal_dokumen" value="{{ old('tanggal_dokumen') }}" class="form-input"><x-input-error name="tanggal_dokumen" /></div>
                <div><label for="file" class="form-label">File</label><input id="file" type="file" name="file" required accept=".pdf,.doc,.docx" class="form-input file:mr-3 file:border-0 file:bg-stone-100 file:px-2 file:py-1 file:text-xs file:font-bold"><p class="mt-1.5 text-xs text-stone-500">PDF/DOC/DOCX · maks. {{ Number::fileSize(config('kawasan.documents.max_size_kb') * 1024) }}</p><x-input-error name="file" /></div>
                <div class="sm:col-span-2 xl:col-span-4"><button class="btn-primary">Unggah dokumen</button></div>
            </form>
        @endcan
        <div class="overflow-x-auto"><table class="data-table min-w-[52rem]"><thead><tr><th>Dokumen</th><th>Nomor / tanggal</th><th>File</th><th>Pengunggah</th><th class="text-right">Aksi</th></tr></thead><tbody>@forelse ($kawasan->documents->sortByDesc('created_at') as $document)<tr><td><span class="badge-neutral">{{ $document->jenis_dokumen->label() }}</span></td><td><p>{{ $document->nomor_dokumen ?: '—' }}</p><p class="mt-1 text-xs text-stone-500">{{ $document->tanggal_dokumen?->translatedFormat('d F Y') ?? 'Tanggal tidak tersedia' }}</p></td><td><p class="max-w-xs truncate font-semibold text-stone-950" title="{{ $document->nama_file }}">{{ $document->nama_file }}</p><p class="mt-1 text-xs text-stone-500">{{ Number::fileSize($document->file_size) }} · {{ $document->mime_type }}</p></td><td>{{ $document->uploader?->name ?? 'Akun terhapus' }}</td><td class="text-right"><div class="inline-flex gap-3"><a href="{{ route('kawasan.dokumen.download', [$kawasan, $document]) }}" class="table-action">Unduh</a>@can('manageDocuments', $kawasan)<form action="{{ route('admin.kawasan.dokumen.destroy', [$kawasan, $document]) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini? File tidak dapat dipulihkan.')">@csrf @method('DELETE')<button class="table-action-danger">Hapus</button></form>@endcan</div></td></tr>@empty<tr><td colspan="5" class="py-10 text-center text-sm text-stone-500">Belum ada dokumen dasar hukum.</td></tr>@endforelse</tbody></table></div>
    </section>
@endsection
