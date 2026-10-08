<?php

namespace App\Http\Controllers;

use App\Models\KawasanPerdesaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenKawasanController extends Controller
{
    public function download(Request $request, int $kawasan, int $document): StreamedResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $visibleKawasan = KawasanPerdesaan::query()->visibleTo($user)->findOrFail($kawasan);
        Gate::authorize('view', $visibleKawasan);
        $visibleDocument = $visibleKawasan->documents()->findOrFail($document);

        return Storage::disk(config('kawasan.documents.disk'))
            ->download($visibleDocument->file_path, $visibleDocument->nama_file);
    }
}
