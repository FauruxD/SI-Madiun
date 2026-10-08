<?php

namespace App\Http\Controllers;

use App\Actions\DeletePotensiMediaAction;
use App\Actions\StorePotensiMediaAction;
use App\Enums\TipeMediaPotensi;
use App\Http\Requests\StoreMediaPotensiRequest;
use App\Models\PotensiDesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PotensiMediaController extends Controller
{
    public function store(
        StoreMediaPotensiRequest $request,
        int $potential,
        StorePotensiMediaAction $storeMedia,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->findOrFail($potential);
        Gate::authorize('manageMedia', $record);

        $storeMedia->execute($record, $request->safe()->except('file'), $request->file('file'), $actor, $request->ip());

        return redirect()->route('potensi-desa.show', $record)->with('status', 'Media potensi berhasil ditambahkan.');
    }

    public function image(Request $request, int $potential, int $media): StreamedResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->findOrFail($potential);
        Gate::authorize('view', $record);
        $image = $record->media()
            ->where('tipe_media', TipeMediaPotensi::Image->value)
            ->whereNotNull('file_path')
            ->findOrFail($media);

        return Storage::disk(config('potensi.images.disk'))->response(
            $image->file_path,
            $image->nama_file,
            ['Content-Type' => $image->mime_type],
        );
    }

    public function destroy(
        Request $request,
        int $potential,
        int $media,
        DeletePotensiMediaAction $deleteMedia,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->findOrFail($potential);
        Gate::authorize('manageMedia', $record);
        $child = $record->media()->findOrFail($media);

        $deleteMedia->execute($child, $actor, $request->ip());

        return redirect()->route('potensi-desa.show', $record)->with('status', 'Media potensi berhasil dihapus.');
    }
}
