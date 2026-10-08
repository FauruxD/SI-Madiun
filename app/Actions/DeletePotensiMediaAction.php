<?php

namespace App\Actions;

use App\Enums\TipeMediaPotensi;
use App\Models\MediaPotensi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeletePotensiMediaAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    public function execute(MediaPotensi $media, User $actor, ?string $ipAddress = null): void
    {
        if ($media->tipe_media === TipeMediaPotensi::Image && $media->file_path !== null) {
            $disk = Storage::disk(config('potensi.images.disk'));

            if ($disk->exists($media->file_path) && ! $disk->delete($media->file_path)) {
                throw new RuntimeException('Foto potensi gagal dihapus dari penyimpanan.');
            }
        }

        DB::transaction(function () use ($media, $actor, $ipAddress): void {
            $this->recordActivity->execute(
                'potential.media.removed',
                $media,
                ['potensi_desa_id' => $media->potensi_desa_id, 'tipe_media' => $media->tipe_media->value],
                $actor,
                $ipAddress,
            );
            $media->delete();
        });
    }
}
