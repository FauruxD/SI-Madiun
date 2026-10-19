<?php

namespace App\Http\Controllers;

use App\Enums\TipeMediaPotensi;
use App\Models\PotensiDesa;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPotensiMediaController extends Controller
{
    public function __invoke(int $potential, int $media): StreamedResponse
    {
        $record = PotensiDesa::query()->published()->findOrFail($potential);
        $image = $record->media()
            ->where('tipe_media', TipeMediaPotensi::Image->value)
            ->whereNotNull('file_path')
            ->findOrFail($media);
        $storage = Storage::disk(config('potensi.images.disk'));

        abort_if($storage->missing($image->file_path), 404);

        return $storage->response(
            $image->file_path,
            $image->nama_file,
            ['Content-Type' => $image->mime_type],
        );
    }
}
