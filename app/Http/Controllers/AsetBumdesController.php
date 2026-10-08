<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SaveAsetBumdesRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AsetBumdesController extends Controller
{
    public function store(SaveAsetBumdesRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $asset = $profile->assets()->create($request->validated());
        $recordActivity->execute('bumdes.asset.created', $asset, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Aset berhasil ditambahkan.');
    }

    public function update(SaveAsetBumdesRequest $request, int $bumdes, int $asset, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visibleAsset = $profile->assets()->findOrFail($asset);
        $visibleAsset->update($request->validated());
        $recordActivity->execute('bumdes.asset.updated', $visibleAsset, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Aset berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $asset, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visibleAsset = $profile->assets()->findOrFail($asset);
        $recordActivity->execute('bumdes.asset.deleted', $visibleAsset, ['bumdes_id' => $profile->id], $actor, $request->ip());
        $visibleAsset->delete();

        return $this->redirect($profile, 'Aset berhasil dihapus.');
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'aset'])->with('status', $message);
    }
}
