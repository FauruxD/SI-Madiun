<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestEProposalRevisionRequest;
use App\Models\EProposal;
use App\Models\User;
use App\Services\EProposalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EProposalWorkflowController extends Controller
{
    public function requestRevision(
        RequestEProposalRevisionRequest $request,
        int $proposal,
        EProposalWorkflowService $workflow,
    ): RedirectResponse {
        return $this->transition($request, $proposal, 'requestRevision', fn (EProposal $record, User $actor): EProposal => $workflow->requestRevision($record, $actor, $request->validated('catatan'), $request->ip()));
    }

    public function verify(Request $request, int $proposal, EProposalWorkflowService $workflow): RedirectResponse
    {
        return $this->transition($request, $proposal, 'verify', fn (EProposal $record, User $actor): EProposal => $workflow->verify($record, $actor, $request->ip()));
    }

    public function publish(Request $request, int $proposal, EProposalWorkflowService $workflow): RedirectResponse
    {
        return $this->transition($request, $proposal, 'publish', fn (EProposal $record, User $actor): EProposal => $workflow->publish($record, $actor, $request->ip()));
    }

    /** @param callable(EProposal, User): EProposal $callback */
    private function transition(Request $request, int $proposal, string $ability, callable $callback): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = EProposal::query()->visibleTo($actor)->findOrFail($proposal);
        Gate::authorize($ability, $record);
        $callback($record, $actor);

        return redirect()->route('admin.eproposal.show', $record)->with('status', 'Status E-Proposal berhasil diperbarui.');
    }
}
