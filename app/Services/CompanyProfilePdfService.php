<?php

namespace App\Services;

use App\Models\Bumdes;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class CompanyProfilePdfService
{
    public function __construct(private BumdesImageService $images) {}

    public function render(Bumdes $bumdes): DomPdf
    {
        $bumdes->loadMissing([
            'desas' => fn ($query) => $query->with('kecamatan.kabupaten')->orderByPivot('is_desa_utama', 'desc')->orderBy('nama'),
            'managers' => fn ($query) => $query->where('is_active', true)->orderBy('urutan')->orderBy('id'),
            'businessUnits' => fn ($query) => $query->with(['products' => fn ($products) => $products->orderBy('nama')->orderBy('id')])->orderBy('nama')->orderBy('id'),
            'assets' => fn ($query) => $query->orderBy('nama')->orderBy('id'),
            'financialSummaries' => fn ($query) => $query->orderByDesc('tahun'),
            'gallery' => fn ($query) => $query->orderBy('urutan')->orderBy('id')->limit(12),
        ]);
        $gallery = $bumdes->gallery->map(fn ($image): array => [
            'title' => $image->judul ?: 'Foto BUMDes',
            'src' => $this->images->dataUri($bumdes->id, 'gallery', $image->file_path),
        ])->filter(fn (array $image): bool => $image['src'] !== null)->values();

        return Pdf::loadView('pdf.company-profile', compact('bumdes', 'gallery'))->setPaper('a4')->setOption([
            'isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false,
        ]);
    }

    public function filename(Bumdes $bumdes): string
    {
        return 'company-profile-'.str($bumdes->nama)->slug('-')->limit(80, '').'-'.$bumdes->id.'.pdf';
    }
}
