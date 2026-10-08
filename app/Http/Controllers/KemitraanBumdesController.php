<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SaveKemitraanBumdesRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KemitraanBumdesController extends Controller
{
    public function store(SaveKemitraanBumdesRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $partnership = $profile->partnerships()->create($request->validated());
        $recordActivity->execute('bumdes.partnership.created', $partnership, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Data kemitraan berhasil ditambahkan.');
    }

    public function update(SaveKemitraanBumdesRequest $request, int $bumdes, int $partnership, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visiblePartnership = $profile->partnerships()->findOrFail($partnership);
        $visiblePartnership->update($request->validated());
        $recordActivity->execute('bumdes.partnership.updated', $visiblePartnership, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Data kemitraan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $partnership, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visiblePartnership = $profile->partnerships()->findOrFail($partnership);
        $recordActivity->execute('bumdes.partnership.deleted', $visiblePartnership, ['bumdes_id' => $profile->id], $actor, $request->ip());
        $visiblePartnership->delete();

        return $this->redirect($profile, 'Data kemitraan berhasil dihapus.');
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'kemitraan'])->with('status', $message);
    }
}
