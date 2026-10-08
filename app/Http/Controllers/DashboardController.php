<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummaryService;
use App\Services\MapFeatureService;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardSummaryService $dashboardSummary,
        MapFeatureService $mapFeatures,
    ): View {
        $user = $request->user();
        abort_if($user === null, 401);
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 5)],
        ]);
        $year = (int) ($validated['tahun'] ?? now()->year);
        $data = $dashboardSummary->forUser($user, $year);
        $data['mapCollection'] = $user->role === UserRole::AdminKawasan
            ? $mapFeatures->collection($user)
            : null;

        return view('dashboard', $data);
    }
}
