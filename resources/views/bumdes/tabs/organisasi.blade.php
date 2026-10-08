<section class="border border-stone-200 bg-white">
    <div class="section-heading border-b border-stone-200 px-5 py-5 sm:px-7"><div><p class="eyebrow">BUM-F-003</p><h2 class="section-title">Struktur organisasi</h2></div><span class="badge-neutral">{{ $bumdes->managers->count() }} pengurus</span></div>
    @can('manageData', $bumdes)
        <form action="{{ route('bumdes.pengurus.store', $bumdes) }}" method="POST" enctype="multipart/form-data" class="grid gap-4 border-b border-stone-200 bg-stone-50 p-5 md:grid-cols-2 xl:grid-cols-4">@csrf
            <div><label for="manager_name" class="form-label">Nama</label><input id="manager_name" name="nama" required class="form-input" value="{{ old('nama') }}"><x-input-error name="nama" /></div>
            <div><label for="manager_position" class="form-label">Jabatan</label><input id="manager_position" name="jabatan" required class="form-input" value="{{ old('jabatan') }}" placeholder="Penasehat, Pengawas, Direktur…"><x-input-error name="jabatan" /></div>
            <div><label for="manager_phone" class="form-label">Telepon</label><input id="manager_phone" name="nomor_telepon" class="form-input" value="{{ old('nomor_telepon') }}"><x-input-error name="nomor_telepon" /></div>
            <div><label for="manager_email" class="form-label">Email</label><input id="manager_email" type="email" name="email" class="form-input" value="{{ old('email') }}"><x-input-error name="email" /></div>
            <div><label for="manager_order" class="form-label">Urutan</label><input id="manager_order" type="number" min="0" name="urutan" required class="form-input" value="{{ old('urutan', 0) }}"><x-input-error name="urutan" /></div>
            <div><label for="manager_photo" class="form-label">Foto</label><input id="manager_photo" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="form-input file:mr-3 file:border-0 file:bg-stone-100 file:px-2 file:py-1 file:text-xs file:font-bold"><x-input-error name="foto" /></div>
            <label class="selection-card self-end"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked class="mt-0.5 size-4"><span><strong class="block text-stone-950">Aktif</strong><span class="mt-1 block text-xs text-stone-500">Tampilkan dalam organisasi.</span></span></label>
            <div class="self-end"><button class="btn-primary w-full">Tambah pengurus</button></div>
        </form>
    @endcan
    <div class="grid gap-px bg-stone-200 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($bumdes->managers as $manager)
            <article class="bg-white p-5 sm:p-6">
                <div class="flex gap-4">@if($manager->foto_path)<img src="{{ route('bumdes.media.pengurus', [$bumdes, $manager]) }}" alt="Foto {{ $manager->nama }}" class="size-16 shrink-0 object-cover">@else<div class="grid size-16 shrink-0 place-items-center bg-stone-100 font-display text-2xl text-stone-400" aria-hidden="true">{{ str($manager->nama)->substr(0, 1) }}</div>@endif<div class="min-w-0"><p class="font-semibold text-stone-950">{{ $manager->nama }}</p><p class="mt-1 text-sm text-emerald-800">{{ $manager->jabatan }}</p><div class="mt-2 flex gap-2"><span @class(['badge-success' => $manager->is_active, 'badge-neutral' => ! $manager->is_active])>{{ $manager->is_active ? 'Aktif' : 'Nonaktif' }}</span><span class="badge-neutral">Urutan {{ $manager->urutan }}</span></div></div></div>
                <p class="mt-4 text-sm text-stone-600">{{ $manager->nomor_telepon ?: 'Telepon belum diisi' }}<br>{{ $manager->email ?: 'Email belum diisi' }}</p>
                @can('manageData', $bumdes)
                    <details class="mt-5 border-t border-stone-200 pt-4"><summary class="cursor-pointer text-sm font-bold text-emerald-800">Ubah pengurus</summary><form action="{{ route('bumdes.pengurus.update', [$bumdes, $manager]) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-3">@csrf @method('PUT')<input name="nama" value="{{ $manager->nama }}" required class="form-input mt-0" aria-label="Nama pengurus"><input name="jabatan" value="{{ $manager->jabatan }}" required class="form-input mt-0" aria-label="Jabatan"><input name="nomor_telepon" value="{{ $manager->nomor_telepon }}" class="form-input mt-0" aria-label="Telepon"><input type="email" name="email" value="{{ $manager->email }}" class="form-input mt-0" aria-label="Email"><input type="number" min="0" name="urutan" value="{{ $manager->urutan }}" required class="form-input mt-0" aria-label="Urutan"><input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="form-input mt-0" aria-label="Ganti foto"><label class="flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($manager->is_active)> Aktif</label><button class="btn-secondary">Simpan</button></form><form action="{{ route('bumdes.pengurus.destroy', [$bumdes, $manager]) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus pengurus ini?')">@csrf @method('DELETE')<button class="table-action-danger">Hapus pengurus</button></form></details>
                @endcan
            </article>
        @empty
            <p class="bg-white p-8 text-sm text-stone-500 md:col-span-2 xl:col-span-3">Belum ada data pengurus.</p>
        @endforelse
    </div>
</section>
