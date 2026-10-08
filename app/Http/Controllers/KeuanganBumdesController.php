<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SaveKeuanganBumdesRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KeuanganBumdesController extends Controller
{
    public function store(SaveKeuanganBumdesRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $summary = $profile->financialSummaries()->create($request->validated());
        $recordActivity->execute('bumdes.financial-summary.created', $summary, ['bumdes_id' => $profile->id, 'tahun' => $summary->tahun], $actor, $request->ip());

        return $this->redirect($profile, 'Ringkasan keuangan berhasil ditambahkan.');
    }

    public function update(SaveKeuanganBumdesRequest $request, int $bumdes, int $keuangan, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $summary = $profile->financialSummaries()->findOrFail($keuangan);
        $summary->update($request->validated());
        $recordActivity->execute('bumdes.financial-summary.updated', $summary, ['bumdes_id' => $profile->id, 'tahun' => $summary->tahun], $actor, $request->ip());

        return $this->redirect($profile, 'Ringkasan keuangan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $keuangan, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $summary = $profile->financialSummaries()->findOrFail($keuangan);
        $recordActivity->execute('bumdes.financial-summary.deleted', $summary, ['bumdes_id' => $profile->id, 'tahun' => $summary->tahun], $actor, $request->ip());
        $summary->delete();

        return $this->redirect($profile, 'Ringkasan keuangan berhasil dihapus.');
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'keuangan'])->with('status', $message);
    }
}
