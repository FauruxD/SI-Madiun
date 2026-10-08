<?php

namespace App\Actions;

use App\Models\DokumenKawasan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteDokumenKawasanAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    public function execute(DokumenKawasan $document, User $actor, ?string $ipAddress = null): void
    {
        $disk = config('kawasan.documents.disk');

        if (! Storage::disk($disk)->delete($document->file_path)) {
            throw new RuntimeException('File dokumen gagal dihapus.');
        }

        DB::transaction(function () use ($document, $actor, $ipAddress): void {
            $this->recordActivity->execute('kawasan.document.removed', $document, [
                'kawasan_id' => $document->kawasan_id,
                'nama_file' => $document->nama_file,
            ], $actor, $ipAddress);

            $document->delete();
        });
    }
}
