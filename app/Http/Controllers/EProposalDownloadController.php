<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Models\EProposal;
use App\Services\ProposalPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class EProposalDownloadController extends Controller
{
    public function admin(Request $request, int $proposal, ProposalPdfService $pdf, RecordActivity $recordActivity): Response
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = EProposal::query()->visibleTo($actor)->findOrFail($proposal);
        Gate::authorize('download', $record);
        $response = $pdf->render($record)->download($pdf->filename($record));
        $recordActivity->execute('eproposal.pdf.downloaded', $record, ['status' => $record->status->value], $actor, $request->ip());

        return $response;
    }

    public function public(int $proposal, ProposalPdfService $pdf): Response
    {
        $record = EProposal::query()->published()->with('potential')->findOrFail($proposal);

        return $pdf->render($record)->download($pdf->filename($record));
    }
}
