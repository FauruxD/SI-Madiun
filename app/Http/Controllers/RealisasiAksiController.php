<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Actions\SaveActionRealization;
use App\Http\Requests\StoreRealisasiAksiRequest;
use App\Http\Requests\UpdateRealisasiAksiRequest;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RealisasiAksiController extends Controller
{
    public function store(
        StoreRealisasiAksiRequest $request,
        int $bumdes,
        int $rencanaAksi,
        SaveActionRealization $saveRealization,
        RecordActivity $recordActivity,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        [$profile, $actionPlan] = $this->manageablePlan($actor, $bumdes, $rencanaAksi);

        DB::transaction(function () use ($actionPlan, $request, $saveRealization, $recordActivity, $actor, $profile): void {
            $realization = $saveRealization->create($actionPlan, $request->validated());
            $recordActivity->execute(
                'action-realization.created',
                $realization,
                ['bumdes_id' => $profile->id, 'rencana_aksi_id' => $actionPlan->id, 'periode_ke' => $realization->periode_ke],
                $actor,
                $request->ip(),
            );
        });

        return $this->redirect($profile, $actionPlan, 'Realisasi periode berhasil dicatat.');
    }

    public function update(
        UpdateRealisasiAksiRequest $request,
        int $bumdes,
        int $rencanaAksi,
        int $realization,
        SaveActionRealization $saveRealization,
        RecordActivity $recordActivity,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        [$profile, $actionPlan] = $this->manageablePlan($actor, $bumdes, $rencanaAksi);
        $progress = $actionPlan->realizations()->findOrFail($realization);

        DB::transaction(function () use ($actionPlan, $progress, $request, $saveRealization, $recordActivity, $actor, $profile): void {
            $previousStatus = $progress->status;
            $progress = $saveRealization->update($actionPlan, $progress, $request->validated());
            $metadata = ['bumdes_id' => $profile->id, 'rencana_aksi_id' => $actionPlan->id, 'periode_ke' => $progress->periode_ke];
            $recordActivity->execute('action-realization.updated', $progress, $metadata, $actor, $request->ip());

            if ($previousStatus !== $progress->status) {
                $recordActivity->execute(
                    'action-realization.status-changed',
                    $progress,
                    $metadata + ['from' => $previousStatus->value, 'to' => $progress->status->value],
                    $actor,
                    $request->ip(),
                );
            }
        });

        return $this->redirect($profile, $actionPlan, 'Realisasi periode berhasil diperbarui.');
    }

    /** @return array{Bumdes, RencanaAksi} */
    private function manageablePlan(User $user, int $bumdesId, int $actionPlanId): array
    {
        $profile = Bumdes::query()->visibleTo($user)->findOrFail($bumdesId);
        $actionPlan = $profile->actionPlans()->findOrFail($actionPlanId);
        Gate::authorize('manageRealizations', $actionPlan);

        return [$profile, $actionPlan];
    }

    private function redirect(Bumdes $bumdes, RencanaAksi $actionPlan, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.rencana-aksi.show', [$bumdes, $actionPlan])->with('status', $message);
    }
}
