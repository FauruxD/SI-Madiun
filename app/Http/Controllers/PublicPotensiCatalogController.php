<?php

namespace App\Http\Controllers;

use App\Models\KategoriPotensi;
use App\Models\PotensiDesa;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicPotensiCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('kategori');
        $potentials = PotensiDesa::query()
            ->published()
            ->with(['village.kecamatan', 'village.kawasans', 'category', 'bumdes', 'coverImage', 'eproposal'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(function (Builder $potentials) use ($search): void {
                    $potentials->where('judul', 'like', $search)
                        ->orWhere('deskripsi', 'like', $search)
                        ->orWhereHas('village', fn (Builder $villages) => $villages->where('nama', 'like', $search));
                });
            })
            ->when($categoryId > 0, fn (Builder $query) => $query->where('kategori_potensi_id', $categoryId))
            ->latest('published_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.potensi.index', [
            'potentials' => $potentials,
            'categories' => KategoriPotensi::query()
                ->whereHas('potentials', fn (Builder $query) => $query->published())
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function show(int $potential): View
    {
        $record = PotensiDesa::query()
            ->published()
            ->with([
                'village.kecamatan.kabupaten',
                'village.kawasans',
                'category',
                'bumdes.website',
                'eproposal',
                'media' => fn ($query) => $query->orderBy('urutan')->orderBy('id'),
            ])
            ->findOrFail($potential);

        return view('public.potensi.show', ['potential' => $record]);
    }
}
