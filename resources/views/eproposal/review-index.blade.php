@extends('layouts.app', ['title' => 'Verifikasi E-Proposal'])

@section('content')
    <div class="border-b border-stone-200 pb-8"><p class="eyebrow">Ruang review</p><h1 class="page-title">Verifikasi E-Proposal</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">Queue ini otomatis dibatasi pada desa dalam kawasan yang menjadi tanggung jawab Anda.</p></div>
    <form class="mt-6 grid gap-3 border border-stone-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]" method="GET">
        <label class="sr-only" for="review-search">Cari proposal</label><input id="review-search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Cari judul atau desa…">
        <label class="sr-only" for="review-status">Status</label><select id="review-status" name="status" class="form-input"><option value="">Semua status</option>@foreach (\App\Enums\EProposalStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        <label class="sr-only" for="review-village">Desa</label><select id="review-village" name="desa_id" class="form-input"><option value="">Semua desa</option>@foreach ($villages as $village)<option value="{{ $village->id }}" @selected((int) request('desa_id') === $village->id)>{{ $village->nama }}</option>@endforeach</select>
        <button class="btn-secondary">Filter</button>
    </form>
    <section class="mt-6 overflow-hidden border border-stone-200 bg-white"><div class="overflow-x-auto"><table class="data-table"><thead><tr><th>Potensi</th><th>Desa</th><th>BUMDes</th><th>Diajukan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse ($proposals as $proposal)<tr><td class="font-semibold"><a href="{{ route('admin.eproposal.review.show', $proposal) }}" class="text-emerald-900 hover:underline">{{ $proposal->potential->judul }}</a><p class="mt-1 text-xs font-normal text-stone-500">{{ $proposal->potential->category->nama }}</p></td><td>{{ $proposal->potential->village->nama }}</td><td>{{ $proposal->potential->bumdes?->nama ?: '—' }}</td><td>{{ $proposal->submitted_at?->translatedFormat('d M Y') ?: '—' }}</td><td><span class="{{ $proposal->status->badgeClass() }}">{{ $proposal->status->label() }}</span></td><td class="text-right"><a href="{{ route('admin.eproposal.review.show', $proposal) }}" class="table-action">Review</a></td></tr>@empty<tr><td colspan="6"><div class="empty-state">Queue review kosong.</div></td></tr>@endforelse
    </tbody></table></div>@if ($proposals->hasPages())<div class="border-t border-stone-200 px-5 py-4">{{ $proposals->links() }}</div>@endif</section>
@endsection
