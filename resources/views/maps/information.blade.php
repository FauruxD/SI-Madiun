@extends('layouts.app', ['title' => 'Titik informasi kawasan'])

@section('content')
<a href="{{ route('peta.geometry.edit', ['kawasan', $kawasan->id]) }}" class="back-link">← Kembali ke kawasan</a>
<p class="eyebrow mt-6">{{ $kawasan->nama }}</p><h1 class="page-title">{{ $point->exists ? 'Edit' : 'Tambah' }} titik informasi</h1>
<p class="page-lead">Tandai fasilitas atau akses penting. Titik aktif tampil di peta publik setelah kawasan ditetapkan.</p>
<form method="POST" action="{{ $point->exists ? route('peta.information.update', [$kawasan->id, $point->id]) : route('peta.information.store', $kawasan->id) }}" class="mt-8">
    @csrf @if($point->exists) @method('PUT') @endif
    <div class="mb-6 grid gap-5 border border-stone-200 bg-white p-5 sm:grid-cols-2">
        <div><label for="info-name" class="form-label">Nama titik</label><input id="info-name" name="nama" value="{{ old('nama', $point->nama) }}" maxlength="120" required class="form-input"></div>
        <div><label for="info-type" class="form-label">Jenis informasi</label><select id="info-type" name="jenis" class="form-input" required>@foreach(\App\Models\TitikInformasi::TYPES as $key => $label)<option value="{{ $key }}" @selected(old('jenis', $point->jenis) === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><label for="info-description" class="form-label">Keterangan singkat (opsional)</label><textarea id="info-description" name="deskripsi" maxlength="500" rows="3" class="form-input">{{ old('deskripsi', $point->deskripsi) }}</textarea></div>
        <div class="sm:col-span-2"><input type="hidden" name="is_active" value="0"><label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $point->exists ? $point->is_active : true)) class="size-4 accent-emerald-800">Tampilkan titik pada peta (kawasan harus ditetapkan)</label></div>
    </div>
    @include('maps.partials.editor', ['geometry' => $point->geometri])
    <div class="mt-5 flex flex-wrap gap-3"><button data-map-save class="btn-primary" disabled>Simpan titik informasi</button><a href="{{ route('peta.geometry.edit', ['kawasan', $kawasan->id]) }}" class="btn-secondary">Batal</a></div>
</form>
@endsection
