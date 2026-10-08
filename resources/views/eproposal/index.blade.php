@extends('layouts.app', ['title' => 'E-Proposal'])

@section('content')
    <div class="flex flex-col gap-5 border-b border-stone-200 pb-8 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="eyebrow">Workflow proposal</p><h1 class="page-title">E-Proposal</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">Ajukan, pantau, dan buka kembali proposal potensi desa dalam satu alur yang tercatat.</p></div>
        <div class="flex flex-wrap gap-3">
            @can('reviewAny', \App\Models\EProposal::class)<a href="{{ route('admin.eproposal.review.index') }}" class="btn-secondary">Queue verifikasi</a>@endcan
            @can('create', \App\Models\EProposal::class)<a href="{{ route('admin.eproposal.create') }}" class="btn-primary">Ajukan E-Proposal</a>@endcan
        </div>
    </div>

    <form class="mt-6 grid gap-3 border border-stone-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_13rem_auto]" method="GET">
        <label class="sr-only" for="search">Cari proposal</label><input id="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Cari judul potensi…">
        <label class="sr-only" for="status">Status</label><select id="status" name="status" class="form-input"><option value="">Semua status</option>@foreach (\App\Enums\EProposalStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        <button class="btn-secondary">Filter</button>
    </form>

    <section class="mt-6 overflow-hidden border border-stone-200 bg-white">
        <div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Potensi</th><th>Desa</th><th>Status</th><th>Diajukan</th><th class="text-right">Aksi</th></tr></thead><tbody>
            @forelse ($proposals as $proposal)
                <tr><td><a class="font-semibold text-emerald-900 hover:underline" href="{{ route('admin.eproposal.show', $proposal) }}">{{ $proposal->potential->judul }}</a><p class="mt-1 text-xs text-stone-500">{{ $proposal->potential->category->nama }}</p></td><td>{{ $proposal->potential->village->nama }}</td><td><span class="{{ $proposal->status->badgeClass() }}">{{ $proposal->status->label() }}</span></td><td>{{ $proposal->submitted_at?->translatedFormat('d M Y') ?: '—' }}</td><td class="text-right"><a href="{{ route('admin.eproposal.show', $proposal) }}" class="table-action">Lihat</a></td></tr>
            @empty
                <tr><td colspan="5"><div class="empty-state">Belum ada E-Proposal dalam kewenangan Anda.</div></td></tr>
            @endforelse
        </tbody></table></div>
        @if ($proposals->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $proposals->links() }}</div>@endif
    </section>
    <section id="proposal-notifications" class="mt-8 scroll-mt-24 border border-stone-200 bg-white p-5 sm:p-6" aria-labelledby="proposal-notifications-heading">
        <h2 id="proposal-notifications-heading" class="section-title">Notifikasi status</h2>
        <p class="mt-2 text-sm text-stone-500">Sepuluh notifikasi belum dibaca terbaru untuk akun Anda.</p>
        <ul class="mt-5 divide-y divide-stone-200">
            @forelse ($proposalNotifications as $notification)
                <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('admin.eproposal.show', (int) $notification->data['eproposal_id']) }}" class="table-action break-words">{{ $notification->data['message'] }}</a>
                        <p class="mt-1 text-xs text-stone-500">{{ $notification->created_at->translatedFormat('d M Y, H.i') }}</p>
                    </div>
                    <form action="{{ route('admin.eproposal.notifications.update', $notification->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn-secondary shrink-0">Tandai dibaca<span class="sr-only">: {{ $notification->data['message'] }}</span></button>
                    </form>
                </li>
            @empty
                <li class="py-4 text-sm text-stone-500">Tidak ada notifikasi yang belum dibaca.</li>
            @endforelse
        </ul>
    </section>
@endsection
