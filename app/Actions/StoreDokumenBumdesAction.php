<?php

namespace App\Actions;

use App\Models\Bumdes;
use App\Models\DokumenBumdes;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreDokumenBumdesAction
{
    public function __construct(private RecordActivity $recordActivity) {}

    /** @param array<string, mixed> $data */
    public function execute(Bumdes $bumdes, array $data, UploadedFile $file, User $actor, ?string $ipAddress = null): DokumenBumdes
    {
        $disk = config('bumdes.documents.disk');
        $path = $file->store('documents/bumdes/'.$bumdes->getKey().'/legalitas', $disk);

        if ($path === false) {
            throw new RuntimeException('Dokumen BUMDes gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($bumdes, $data, $file, $actor, $ipAddress, $path): DokumenBumdes {
                $document = $bumdes->documents()->create([
                    ...$data,
                    'nama_file' => $this->safeOriginalName($file),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => (int) $file->getSize(),
                    'uploaded_by' => $actor->getKey(),
                ]);

                $this->recordActivity->execute('bumdes.document.uploaded', $document, [
                    'bumdes_id' => $bumdes->getKey(),
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
