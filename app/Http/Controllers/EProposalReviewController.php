<?php

namespace App\Http\Controllers;

use App\Enums\EProposalStatus;
use App\Models\Desa;
use App\Models\EProposal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EProposalReviewController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        Gate::authorize('reviewAny', EProposal::class);
        $status = EProposalStatus::tryFrom($request->string('status')->value());
        $villageId = $request->integer('desa_id');

        $proposals = EProposal::query()
            ->visibleTo($actor)
            ->with(['potential.village.kecamatan', 'potential.category', 'potential.bumdes', 'submitter'])
            ->when($villageId > 0, fn (Builder $query) => $query->whereHas('potential', fn (Builder $potentials) => $potentials->where('desa_id', $villageId)))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status->value))
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->whereHas('potential', fn (Builder $potentials) => $potentials
                    ->where('judul', 'like', $search)
                    ->orWhereHas('village', fn (Builder $villages) => $villages->where('nama', 'like', $search)));
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('eproposal.review-index', [
            'proposals' => $proposals,
            'villages' => Desa::query()
                ->when(! $actor->isSuperAdmin(), fn (Builder $query) => $query->whereHas('kawasans', fn (Builder $kawasans) => $kawasans->visibleTo($actor)))
                ->orderBy('nama')->orderBy('id')->get(['id', 'nama']),
        ]);
    }
}
