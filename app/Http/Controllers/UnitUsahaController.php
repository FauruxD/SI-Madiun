<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SaveUnitUsahaRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class UnitUsahaController extends Controller
{
    public function store(SaveUnitUsahaRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->create($request->validated());
        $recordActivity->execute('bumdes.business-unit.created', $unit, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Unit usaha berhasil ditambahkan.');
    }

    public function update(SaveUnitUsahaRequest $request, int $bumdes, int $unitUsaha, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->findOrFail($unitUsaha);
        $unit->update($request->validated());
        $recordActivity->execute('bumdes.business-unit.updated', $unit, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Unit usaha berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $unitUsaha, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->with('products')->findOrFail($unitUsaha);
        $unit->products->pluck('foto_path')->filter()->each(fn (string $path) => Storage::disk(config('bumdes.images.disk'))->delete($path));
        $recordActivity->execute('bumdes.business-unit.deleted', $unit, ['bumdes_id' => $profile->id], $actor, $request->ip());
        $unit->delete();

        return $this->redirect($profile, 'Unit usaha berhasil dihapus.');
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'unit-usaha'])->with('status', $message);
    }
}
