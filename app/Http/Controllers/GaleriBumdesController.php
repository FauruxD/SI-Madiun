<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Enums\TipeMediaBumdes;
use App\Http\Requests\StoreGaleriBumdesRequest;
use App\Models\Bumdes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GaleriBumdesController extends Controller
{
    public function store(StoreGaleriBumdesRequest $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $file = $request->file('file');
        $path = $file->store('images/bumdes/'.$profile->id.'/galeri', config('bumdes.images.disk'));

        if ($path === false) {
            throw new RuntimeException('Foto galeri gagal disimpan.');
        }

        try {
            $image = $profile->gallery()->create([
                ...$request->safe()->except('file'),
                'tipe_media' => TipeMediaBumdes::Image,
                'nama_file' => $this->safeOriginalName($file->getClientOriginalName()),
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => (int) $file->getSize(),
                'uploaded_by' => $actor->id,
            ]);
        } catch (Throwable $exception) {
            Storage::disk(config('bumdes.images.disk'))->delete($path);
            throw $exception;
        }

        $recordActivity->execute('bumdes.gallery.created', $image, ['bumdes_id' => $profile->id], $actor, $request->ip());

        return $this->redirect($profile, 'Foto galeri berhasil ditambahkan.');
    }

    public function destroy(Request $request, int $bumdes, int $gallery, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = $this->manageableBumdes($actor, $bumdes);
        $image = $profile->gallery()->findOrFail($gallery);
        $disk = Storage::disk(config('bumdes.images.disk'));

        if ($disk->exists($image->file_path) && ! $disk->delete($image->file_path)) {
            throw new RuntimeException('Foto galeri gagal dihapus dari penyimpanan.');
        }

        $recordActivity->execute('bumdes.gallery.deleted', $image, ['bumdes_id' => $profile->id], $actor, $request->ip());
        $image->delete();

        return $this->redirect($profile, 'Foto galeri berhasil dihapus.');
    }

    private function manageableBumdes(User $user, int $id): Bumdes
    {
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('manageData', $bumdes);

        return $bumdes;
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'galeri';

        return mb_substr($name, 0, 255);
    }

    private function redirect(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'galeri'])->with('status', $message);
    }
}
