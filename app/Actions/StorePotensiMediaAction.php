<?php

namespace App\Actions;

use App\Enums\TipeMediaPotensi;
use App\Models\MediaPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StorePotensiMediaAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(
        PotensiDesa $potential,
        array $data,
        ?UploadedFile $file,
        User $actor,
        ?string $ipAddress = null,
    ): MediaPotensi {
        $type = TipeMediaPotensi::from((string) $data['tipe_media']);

        if ($type === TipeMediaPotensi::Video) {
            return $this->storeVideo($potential, $data, $actor, $ipAddress);
        }

        if ($file === null) {
            throw new RuntimeException('Foto potensi tidak ditemukan.');
        }

        $disk = config('potensi.images.disk');
        $path = $file->store('media/potensi/'.$potential->getKey().'/images', $disk);

        if ($path === false) {
            throw new RuntimeException('Foto potensi gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($potential, $data, $file, $actor, $ipAddress, $path): MediaPotensi {
                $media = $potential->media()->create([
                    'tipe_media' => TipeMediaPotensi::Image,
                    'judul' => $data['judul'] ?? null,
                    'nama_file' => $this->safeOriginalName($file),
                    'file_path' => $path,
                    'media_url' => null,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                    'urutan' => $data['urutan'],
                    'uploaded_by' => $actor->getKey(),
                ]);

                $this->recordActivity->execute(
                    'potential.media.uploaded',
                    $media,
                    ['potensi_desa_id' => $potential->getKey(), 'tipe_media' => TipeMediaPotensi::Image->value],
                    $actor,
                    $ipAddress,
                );

                return $media;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    private function storeVideo(PotensiDesa $potential, array $data, User $actor, ?string $ipAddress): MediaPotensi
    {
        return DB::transaction(function () use ($potential, $data, $actor, $ipAddress): MediaPotensi {
            $media = $potential->media()->create([
                'tipe_media' => TipeMediaPotensi::Video,
                'judul' => $data['judul'] ?? null,
                'media_url' => $data['media_url'],
                'urutan' => $data['urutan'],
                'uploaded_by' => $actor->getKey(),
            ]);

            $this->recordActivity->execute(
                'potential.media.uploaded',
                $media,
                ['potensi_desa_id' => $potential->getKey(), 'tipe_media' => TipeMediaPotensi::Video->value],
                $actor,
                $ipAddress,
            );

            return $media;
        });
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'foto-potensi';

        return mb_substr($name, 0, 255);
    }
}
