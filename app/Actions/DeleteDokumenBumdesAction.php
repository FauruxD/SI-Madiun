<?php

namespace App\Actions;

use App\Models\DokumenBumdes;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteDokumenBumdesAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    public function execute(DokumenBumdes $document, User $actor, ?string $ipAddress = null): void
    {
        if (! Storage::disk(config('bumdes.documents.disk'))->delete($document->file_path)) {
            throw new RuntimeException('File dokumen BUMDes gagal dihapus.');
        }

        DB::transaction(function () use ($document, $actor, $ipAddress): void {
            $this->recordActivity->execute('bumdes.document.removed', $document, [
                'bumdes_id' => $document->bumdes_id,
                'nama_file' => $document->nama_file,
            ], $actor, $ipAddress);
            $document->delete();
        });
    }
}
