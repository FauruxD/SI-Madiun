<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BumdesImageService
{
    /** @return array{contents: string, mime: string}|null */
    public function read(int $bumdesId, string $kind, ?string $path): ?array
    {
        $folders = ['gallery' => 'galeri', 'manager' => 'pengurus', 'product' => 'produk'];
        $folder = $folders[$kind] ?? null;
        if ($folder === null || $path === null || ! preg_match('#^images/bumdes/'.$bumdesId.'/'.$folder.'/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp)$#iD', $path)) {
            return null;
        }

        $storage = Storage::disk(config('bumdes.images.disk'));
        if (! $storage->exists($path) || $storage->size($path) > (int) config('bumdes.images.max_size_kb') * 1024) {
            return null;
        }

        $contents = $storage->get($path);
        if ($contents === null || $contents === '') {
            return null;
        }
        $image = @getimagesizefromstring($contents);
        $mime = $image['mime'] ?? null;
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        return ['contents' => $contents, 'mime' => $mime];
    }

    public function dataUri(int $bumdesId, string $kind, ?string $path): ?string
    {
        $image = $this->read($bumdesId, $kind, $path);

        return $image === null ? null : 'data:'.$image['mime'].';base64,'.base64_encode($image['contents']);
    }
}
