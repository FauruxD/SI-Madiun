<?php

namespace App\Actions;

use App\Models\DokumenKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreDokumenKawasanAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(
        KawasanPerdesaan $kawasan,
        array $data,
        UploadedFile $file,
        User $actor,
        ?string $ipAddress = null,
    ): DokumenKawasan {
        $disk = config('kawasan.documents.disk');
        $path = $file->store('kawasan-documents/'.$kawasan->getKey(), $disk);

        if ($path === false) {
            throw new RuntimeException('Dokumen gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($kawasan, $data, $file, $actor, $ipAddress, $path): DokumenKawasan {
                $document = $kawasan->documents()->create([
                    ...$data,
                    'nama_file' => $this->safeOriginalName($file),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                    'uploaded_by' => $actor->getKey(),
                ]);

                $this->recordActivity->execute('kawasan.document.uploaded', $document, [
                    'kawasan_id' => $kawasan->getKey(),
                    'jenis_dokumen' => $document->jenis_dokumen->value,
                ], $actor, $ipAddress);

                return $document;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'dokumen';

        return mb_substr($name, 0, 255);
    }
}
