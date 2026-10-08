<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SavePengurusBumdesRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PengurusBumdesController extends Controller
{
    public function store(SavePengurusBumdesRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $path = $this->storePhoto($request, $profile);

        try {
            $manager = $profile->managers()->create([...$request->safe()->except('foto'), 'foto_path' => $path]);
        } catch (Throwable $exception) {
            $this->deletePhoto($path);
            throw $exception;
        }

        $recordActivity->execute('bumdes.manager.created', $manager, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Pengurus berhasil ditambahkan.');
    }

    public function update(SavePengurusBumdesRequest $request, int $bumdes, int $manager, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visibleManager = $profile->managers()->findOrFail($manager);
        $newPath = $this->storePhoto($request, $profile);
        $oldPath = $visibleManager->foto_path;

        try {
            $visibleManager->update([...$request->safe()->except('foto'), 'foto_path' => $newPath ?? $oldPath]);
        } catch (Throwable $exception) {
            $this->deletePhoto($newPath);
            throw $exception;
        }

        if ($newPath !== null) {
            $this->deletePhoto($oldPath);
        }

        $recordActivity->execute('bumdes.manager.updated', $visibleManager, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $manager, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $visibleManager = $profile->managers()->findOrFail($manager);
        $this->deletePhoto($visibleManager->foto_path);
        $recordActivity->execute('bumdes.manager.deleted', $visibleManager, ['bumdes_id' => $profile->id], $actor, $request->ip());
        $visibleManager->delete();

        return $this->redirect($profile, 'Pengurus berhasil dihapus.');
    }

    private function storePhoto(SavePengurusBumdesRequest $request, Bumdes $bumdes): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        $path = $request->file('foto')->store('images/bumdes/'.$bumdes->id.'/pengurus', config('bumdes.images.disk'));

        if ($path === false) {
            throw new RuntimeException('Foto pengurus gagal disimpan.');
        }

        return $path;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path !== null) {
            Storage::disk(config('bumdes.images.disk'))->delete($path);
        }
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'organisasi'])->with('status', $message);
    }
}
