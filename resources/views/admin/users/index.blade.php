@extends('layouts.app', ['title' => 'Pengguna'])

@section('content')
    <div class="section-heading">
        <div><p class="eyebrow">Administrasi akses</p><h1 class="page-title">Pengguna</h1><p class="page-lead">Kelola akun, role, status, dan penugasan desa.</p></div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">Tambah pengguna</a>
    </div>

    <form method="GET" class="mt-8 grid gap-3 border border-stone-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_13rem_auto]">
        <div><label for="search" class="sr-only">Cari pengguna</label><input id="search" name="search" value="{{ request('search') }}" class="form-input mt-0" placeholder="Cari nama, email, username…"></div>
        <div><label for="role" class="sr-only">Filter role</label><select id="role" name="role" class="form-input mt-0"><option value="">Semua role</option>@foreach ($roles as $role)<option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>@endforeach</select></div>
        <button class="btn-secondary">Terapkan</button>
    </form>

    <div class="mt-5 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Pengguna</th><th>Role</th><th>Cakupan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            <td><p class="font-semibold text-stone-950">{{ $managedUser->name }}</p><p class="mt-1 text-xs text-stone-500">{{ '@'.$managedUser->username }} · {{ $managedUser->email }}</p></td>
                            <td><span class="badge-neutral">{{ $managedUser->role->label() }}</span></td>
                            <td><p class="text-sm text-stone-700">{{ $managedUser->desas->pluck('nama')->join(', ') ?: '—' }}</p></td>
                            <td><span @class(['badge-success' => $managedUser->is_active, 'badge-danger' => ! $managedUser->is_active])>{{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-right"><a href="{{ route('admin.users.edit', $managedUser) }}" class="table-action">Ubah</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-sm text-stone-500">Tidak ada pengguna yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $users->links() }}</div>@endif
    </div>
@endsection
