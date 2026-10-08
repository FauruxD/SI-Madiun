<?php

namespace App\Http\Controllers;

use App\Enums\EProposalStatus;
use App\Enums\PotensiStatus;
use App\Http\Requests\ResubmitEProposalRequest;
use App\Http\Requests\StoreEProposalRequest;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\PotensiDesa;
use App\Models\User;
use App\Notifications\EProposalStatusChanged;
use App\Services\EProposalWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EProposalController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        Gate::authorize('viewAny', EProposal::class);
        $status = EProposalStatus::tryFrom($request->string('status')->value());

        $proposals = EProposal::query()
            ->visibleTo($actor)
            ->with(['potential.village.kecamatan', 'potential.category', 'potential.bumdes', 'submitter'])
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status->value))
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->whereHas('potential', fn (Builder $potentials) => $potentials->where('judul', 'like', $search));
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('eproposal.index', [
            'proposals' => $proposals,
            'proposalNotifications' => $actor->unreadNotifications()
                ->where('type', EProposalStatusChanged::class)
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        Gate::authorize('create', EProposal::class);

        $potentials = PotensiDesa::query()
            ->visibleTo($actor)
            ->whereDoesntHave('eproposal')
            ->where('status', PotensiStatus::Draft)
            ->with(['village.kecamatan', 'category'])
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $selectedPotential = null;
        $potentialId = (int) $request->old('potensi_desa_id', $request->integer('potensi_desa_id'));

        if ($potentialId > 0) {
            $selectedPotential = $this->visiblePotential($actor, $potentialId);
            abort_if($selectedPotential->status !== PotensiStatus::Draft || $selectedPotential->eproposal()->exists(), 404);
            $selectedPotential->load(['village.kecamatan', 'village.kawasans', 'category', 'bumdes', 'media']);
        }

        return view('eproposal.create', ['potentials' => $potentials, 'selectedPotential' => $selectedPotential]);
    }

    public function store(StoreEProposalRequest $request, EProposalWorkflowService $workflow): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $potential = $this->visiblePotential($actor, (int) $request->validated('potensi_desa_id'));
        Gate::authorize('submitPotential', [EProposal::class, $potential]);
        $proposal = $workflow->submit($potential, $actor, $request->validated('catatan_pengaju'), $request->ip());

        return redirect()->route('admin.eproposal.show', $proposal)->with('status', 'E-Proposal berhasil diajukan.');
    }

    public function show(Request $request, int $proposal): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = EProposal::query()
            ->visibleTo($actor)
            ->with([
                'potential.village.kecamatan.kabupaten',
                'potential.village.kawasans',
                'potential.category',
                'potential.bumdes',
                'potential.media' => fn ($query) => $query->orderBy('urutan')->orderBy('id'),
                'submitter',
                'reviews.reviewer',
            ])
            ->findOrFail($proposal);
        Gate::authorize('view', $record);
        if ($request->routeIs('admin.eproposal.review.show')) {
            Gate::authorize('review', $record);
        }

        return view('eproposal.show', ['proposal' => $record]);
    }

    public function resubmit(
        ResubmitEProposalRequest $request,
        int $proposal,
        EProposalWorkflowService $workflow,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = EProposal::query()->visibleTo($actor)->findOrFail($proposal);
        Gate::authorize('resubmit', $record);
        $workflow->resubmit($record, $actor, $request->validated('catatan_pengaju'), $request->ip());

        return redirect()->route('admin.eproposal.show', $record)->with('status', 'E-Proposal berhasil dikirim ulang.');
    }

    private function visiblePotential(User $actor, int $potentialId): PotensiDesa
    {
        $village = Desa::query()->visibleTo($actor)->whereHas('potentials', fn (Builder $query) => $query->whereKey($potentialId))->firstOrFail();

        return $village->potentials()->whereKey($potentialId)->firstOrFail();
    }
}
