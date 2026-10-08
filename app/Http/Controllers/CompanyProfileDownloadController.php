<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Models\Bumdes;
use App\Services\CompanyProfilePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CompanyProfileDownloadController extends Controller
{
    public function admin(Request $request, int $bumdes, CompanyProfilePdfService $pdf, RecordActivity $activity): Response
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $profile = Bumdes::query()->visibleTo($user)->findOrFail($bumdes);
        Gate::authorize('downloadCompanyProfile', $profile);
        $response = $pdf->render($profile)->download($pdf->filename($profile));
        $response->headers->set('Cache-Control', 'private, no-store');
        $activity->execute('bumdes.company-profile.downloaded', $profile, user: $user, ipAddress: $request->ip());

        return $response;
    }

    /** Public publication authority and financial disclosure rules remain undefined in SRS/PRD. */
    public function public(string $subdomain): never
    {
        abort(404);
    }
}
