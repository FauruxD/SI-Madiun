<?php

namespace App\Services;

use App\Enums\TipeMediaPotensi;
use App\Models\EProposal;
use App\Models\MediaPotensi;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;

class ProposalPdfService
{
    public function render(EProposal $proposal): DomPdf
    {
        $proposal->loadMissing([
            'potential.village.kecamatan.kabupaten',
            'potential.village.kawasans',
            'potential.category',
            'potential.bumdes',
            'potential.media' => fn ($query) => $query->orderBy('urutan')->orderBy('id'),
            'submitter',
        ]);

        abort_unless($proposal->status->isDownloadable(), 404);

        $images = $proposal->potential->media
            ->filter(fn (MediaPotensi $media): bool => $media->tipe_media === TipeMediaPotensi::Image && filled($media->file_path))
            ->map(fn (MediaPotensi $media): array => [
                'title' => $media->judul ?: 'Foto potensi',
                'src' => $this->imageDataUri($media),
            ])
            ->filter(fn (array $image): bool => filled($image['src']))
            ->values();

        return Pdf::loadView('pdf.eproposal', [
            'proposal' => $proposal,
            'potential' => $proposal->potential,
            'images' => $images,
            'status' => $proposal->status,
        ])->setPaper('a4')->setOption([
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
        ]);
    }

    public function filename(EProposal $proposal): string
    {
        $slug = str($proposal->potential->judul)->slug('-');

        return 'eproposal-'.$slug.'-'.$proposal->getKey().'.pdf';
    }

    private function imageDataUri(MediaPotensi $media): ?string
    {
        $path = $media->file_path;
        if (! is_string($path) || ! preg_match('#^media/potensi/'.$media->potensi_desa_id.'/images/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp)$#iD', $path)) {
            return null;
        }

        $disk = (string) config('potensi.images.disk', 'local');
        $storage = Storage::disk($disk);

        if (! $storage->exists($path) || $storage->size($path) > (int) config('potensi.images.max_size_kb', 5120) * 1024) {
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

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
