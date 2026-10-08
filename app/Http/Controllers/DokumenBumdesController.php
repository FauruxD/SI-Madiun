<?php

namespace App\Http\Controllers;

use App\Actions\DeleteDokumenBumdesAction;
use App\Actions\StoreDokumenBumdesAction;
use App\Http\Requests\StoreDokumenBumdesRequest;
use App\Models\Bumdes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenBumdesController extends Controller
{
    public function store(StoreDokumenBumdesRequest $request, int $bumdes, StoreDokumenBumdesAction $storeDocument): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        Gate::authorize('manageDocuments', $profile);

        $storeDocument->execute($profile, $request->safe()->except('file'), $request->file('file'), $actor, $request->ip());

        return $this->toDocuments($profile, 'Dokumen legalitas berhasil diunggah.');
    }

    public function download(Request $request, int $bumdes, int $document): StreamedResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $profile = Bumdes::query()->visibleTo($user)->findOrFail($bumdes);
        Gate::authorize('view', $profile);
        $visibleDocument = $profile->documents()->findOrFail($document);

        return Storage::disk(config('bumdes.documents.disk'))->download($visibleDocument->file_path, $visibleDocument->nama_file);
    }

    public function destroy(Request $request, int $bumdes, int $document, DeleteDokumenBumdesAction $deleteDocument): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        Gate::authorize('manageDocuments', $profile);
        $visibleDocument = $profile->documents()->findOrFail($document);
        $deleteDocument->execute($visibleDocument, $actor, $request->ip());

        return $this->toDocuments($profile, 'Dokumen legalitas berhasil dihapus.');
    }

    private function toDocuments(Bumdes $bumdes, string $message): RedirectResponse
    {
        return redirect()->route('bumdes.show', ['bumdes' => $bumdes, 'tab' => 'dokumen'])->with('status', $message);
    }
}
