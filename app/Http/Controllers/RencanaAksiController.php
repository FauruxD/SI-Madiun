<?php

namespace App\Http\Controllers;

use App\Actions\ActionAchievementCalculator;
use App\Actions\RecalculateActionAchievements;
use App\Actions\RecordActivity;
use App\Enums\AchievementStatus;
use App\Enums\ActionPeriod;
use App\Http\Requests\StoreRencanaAksiRequest;
use App\Http\Requests\UpdateRencanaAksiRequest;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RencanaAksiController extends Controller
{
    public function index(Request $request, ActionAchievementCalculator $calculator): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        Gate::authorize('viewAny', RencanaAksi::class);

        $year = $request->integer('tahun');
        $year = $year >= 2000 && $year <= ((int) date('Y') + 10) ? $year : (int) date('Y');
        $plans = RencanaAksi::query()
            ->visibleTo($user)
            ->with(['bumdes', 'realizations' => fn ($query) => $query->orderBy('periode_ke')])
            ->where('tahun', $year)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(function ($plans) use ($search): void {
                    $plans->where('program', 'like', $search)
                        ->orWhere('kegiatan', 'like', $search)
                        ->orWhereHas('bumdes', fn ($bumdes) => $bumdes->where('nama', 'like', $search));
                });
            })
            ->orderBy('program')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
        $snapshots = $plans->getCollection()->mapWithKeys(
            fn (RencanaAksi $plan): array => [$plan->id => $calculator->summarize($plan->target_nilai, $plan->realizations)],
        );
        $availableYears = RencanaAksi::query()
            ->visibleTo($user)
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->push((int) date('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        return view('rencana-aksi.index', compact('plans', 'snapshots', 'availableYears', 'year'));
    }

    public function create(Request $request, int $bumdes): View
    {
        $profile = $this->visibleBumdes($request, $bumdes);
        Gate::authorize('create', [RencanaAksi::class, $profile]);

        return view('rencana-aksi.create', [
            'bumdes' => $profile,
            'periodOptions' => ActionPeriod::cases(),
        ]);
    }

    public function store(StoreRencanaAksiRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        Gate::authorize('create', [RencanaAksi::class, $profile]);

        $actionPlan = DB::transaction(function () use ($profile, $request, $actor, $recordActivity): RencanaAksi {
            $actionPlan = $profile->actionPlans()->create($request->validated());
            $recordActivity->execute(
                'action-plan.created',
                $actionPlan,
                ['bumdes_id' => $profile->id, 'tahun' => $actionPlan->tahun],
                $actor,
                $request->ip(),
            );

            return $actionPlan;
        });

        return redirect()->route('bumdes.rencana-aksi.show', [$profile, $actionPlan])
            ->with('status', 'Rencana aksi berhasil dibuat.');
    }

    public function show(Request $request, int $bumdes, int $rencanaAksi, ActionAchievementCalculator $calculator): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        $actionPlan = $profile->actionPlans()->with(['realizations' => fn ($query) => $query->orderBy('periode_ke')])->findOrFail($rencanaAksi);
        Gate::authorize('view', $actionPlan);

        return view('rencana-aksi.show', [
            'bumdes' => $profile,
            'actionPlan' => $actionPlan,
            'snapshot' => $calculator->summarize($actionPlan->target_nilai, $actionPlan->realizations),
            'realizationsByPeriod' => $actionPlan->realizations->keyBy('periode_ke'),
            'periodNumbers' => range(1, $actionPlan->periode->maximumPeriods()),
            'manualStatuses' => [
                AchievementStatus::InProgress,
                AchievementStatus::Completed,
                AchievementStatus::Delayed,
            ],
        ]);
    }

    public function edit(Request $request, int $bumdes, int $rencanaAksi): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        $actionPlan = $profile->actionPlans()->findOrFail($rencanaAksi);
        Gate::authorize('update', $actionPlan);

        return view('rencana-aksi.edit', [
            'bumdes' => $profile,
            'actionPlan' => $actionPlan,
            'periodOptions' => ActionPeriod::cases(),
        ]);
    }

    public function update(
        UpdateRencanaAksiRequest $request,
        int $bumdes,
        int $rencanaAksi,
        RecalculateActionAchievements $recalculate,
        RecordActivity $recordActivity,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        $actionPlan = $profile->actionPlans()->findOrFail($rencanaAksi);
        Gate::authorize('update', $actionPlan);

        DB::transaction(function () use ($actionPlan, $request, $recalculate, $recordActivity, $actor, $profile): void {
            $targetChanged = (string) $actionPlan->target_nilai !== (string) $request->validated('target_nilai');
            $actionPlan->update($request->validated());

            if ($targetChanged) {
                $recalculate->execute($actionPlan);
            }

            $recordActivity->execute(
                'action-plan.updated',
                $actionPlan,
                ['bumdes_id' => $profile->id, 'tahun' => $actionPlan->tahun],
                $actor,
                $request->ip(),
            );
        });

        return redirect()->route('bumdes.rencana-aksi.show', [$profile, $actionPlan])
            ->with('status', 'Rencana aksi berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $rencanaAksi, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        $actionPlan = $profile->actionPlans()->findOrFail($rencanaAksi);
        Gate::authorize('delete', $actionPlan);

        if ($actionPlan->realizations()->exists()) {
            return back()->withErrors(['rencana_aksi' => 'Rencana aksi yang sudah memiliki realisasi tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($actionPlan, $recordActivity, $actor, $request, $profile): void {
            $recordActivity->execute(
                'action-plan.deleted',
                $actionPlan,
                ['bumdes_id' => $profile->id, 'tahun' => $actionPlan->tahun],
                $actor,
                $request->ip(),
            );
            $actionPlan->delete();
        });

        return redirect()->route('rencana-aksi.index', ['tahun' => $actionPlan->tahun])
            ->with('status', 'Rencana aksi berhasil dihapus.');
    }

    private function visibleBumdes(Request $request, int $id): Bumdes
    {
        $user = $request->user();
        abort_if($user === null, 401);

        return Bumdes::query()->visibleTo($user)->findOrFail($id);
    }
}
