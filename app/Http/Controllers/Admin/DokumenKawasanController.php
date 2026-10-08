<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteDokumenKawasanAction;
use App\Actions\StoreDokumenKawasanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDokumenKawasanRequest;
use App\Models\DokumenKawasan;
use App\Models\KawasanPerdesaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DokumenKawasanController extends Controller
{
    public function store(
        StoreDokumenKawasanRequest $request,
        KawasanPerdesaan $kawasan,
        StoreDokumenKawasanAction $storeDocument,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);

        $storeDocument->execute(
            $kawasan,
            $request->safe()->except('file'),
            $request->file('file'),
            $actor,
            $request->ip(),
        );

        return redirect()->route('admin.kawasan.show', $kawasan)->with('status', 'Dokumen dasar hukum berhasil diunggah.');
    }

    public function destroy(
        Request $request,
        KawasanPerdesaan $kawasan,
        DokumenKawasan $document,
        DeleteDokumenKawasanAction $deleteDocument,
    ): RedirectResponse {
        Gate::authorize('manageDocuments', $kawasan);
        $scopedDocument = $kawasan->documents()->findOrFail($document->getKey());
        $actor = $request->user();
        abort_if($actor === null, 401);

        $deleteDocument->execute($scopedDocument, $actor, $request->ip());

        return redirect()->route('admin.kawasan.show', $kawasan)->with('status', 'Dokumen berhasil dihapus.');
    }
}
