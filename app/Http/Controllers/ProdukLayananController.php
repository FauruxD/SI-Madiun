<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\SaveProdukLayananRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProdukLayananController extends Controller
{
    public function store(SaveProdukLayananRequest $request, int $bumdes, int $unitUsaha, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->findOrFail($unitUsaha);
        $path = $this->storePhoto($request, $profile);

        try {
            $product = $unit->products()->create([...$request->safe()->except('foto'), 'foto_path' => $path]);
        } catch (Throwable $exception) {
            $this->deletePhoto($path);
            throw $exception;
        }

        $recordActivity->execute('bumdes.product.created', $product, ['bumdes_id' => $profile->id, 'unit_usaha_id' => $unit->id], $actor, $request->ip());

        return $this->redirect($profile, 'Produk/layanan berhasil ditambahkan.');
    }

    public function update(SaveProdukLayananRequest $request, int $bumdes, int $unitUsaha, int $product, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->findOrFail($unitUsaha);
        $visibleProduct = $unit->products()->findOrFail($product);
        $newPath = $this->storePhoto($request, $profile);
        $oldPath = $visibleProduct->foto_path;

        try {
            $visibleProduct->update([...$request->safe()->except('foto'), 'foto_path' => $newPath ?? $oldPath]);
        } catch (Throwable $exception) {
            $this->deletePhoto($newPath);
            throw $exception;
        }

        if ($newPath !== null) {
            $this->deletePhoto($oldPath);
        }

        $recordActivity->execute('bumdes.product.updated', $visibleProduct, ['bumdes_id' => $profile->id, 'unit_usaha_id' => $unit->id], $actor, $request->ip());

        return $this->redirect($profile, 'Produk/layanan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, int $unitUsaha, int $product, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $unit = $profile->businessUnits()->findOrFail($unitUsaha);
        $visibleProduct = $unit->products()->findOrFail($product);
        $this->deletePhoto($visibleProduct->foto_path);
        $recordActivity->execute('bumdes.product.deleted', $visibleProduct, ['bumdes_id' => $profile->id, 'unit_usaha_id' => $unit->id], $actor, $request->ip());
        $visibleProduct->delete();

        return $this->redirect($profile, 'Produk/layanan berhasil dihapus.');
    }

    private function storePhoto(SaveProdukLayananRequest $request, Bumdes $bumdes): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        $path = $request->file('foto')->store('images/bumdes/'.$bumdes->id.'/produk', config('bumdes.images.disk'));

        if ($path === false) {
            throw new RuntimeException('Foto produk gagal disimpan.');
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
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'produk'])->with('status', $message);
    }
}
