<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $metrics = match ($user->role) {
            UserRole::SuperAdmin => [
                ['label' => 'Akun aktif', 'value' => User::query()->where('is_active', true)->count(), 'meta' => 'seluruh role'],
                ['label' => 'Kabupaten', 'value' => Kabupaten::query()->count(), 'meta' => 'master wilayah'],
                ['label' => 'Kecamatan', 'value' => Kecamatan::query()->count(), 'meta' => 'master wilayah'],
                ['label' => 'Desa', 'value' => Desa::query()->count(), 'meta' => 'master wilayah'],
            ],
            UserRole::AdminDesa => [
                ['label' => 'Desa ditugaskan', 'value' => $user->desas()->count(), 'meta' => 'sesuai kewenangan'],
            ],
            UserRole::AdminKawasan => [
                ['label' => 'Status modul', 'value' => 'Siap', 'meta' => 'assignment kawasan hadir di Phase 2'],
            ],
        };

        return view('dashboard', [
            'metrics' => $metrics,
            'assignedDesas' => $user->role === UserRole::AdminDesa
                ? $user->desas()->with('kecamatan.kabupaten')->orderBy('nama')->get()
                : collect(),
            'recentActivities' => $user->isSuperAdmin()
                ? ActivityLog::query()->with('user')->latest()->limit(6)->get()
                : collect(),
        ]);
    }
}
