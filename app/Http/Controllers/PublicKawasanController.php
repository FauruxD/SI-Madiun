<?php

namespace App\Http\Controllers;

use App\Models\KawasanPerdesaan;
use App\Models\PotensiDesa;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicKawasanController extends Controller
{
    public function index(Request $request): View
    {
        $kawasans = KawasanPerdesaan::query()
            ->established()
            ->with('categories')
            ->withCount('desas')
            ->when($request->filled('search'), fn (Builder $query) => $query->where('nama', 'like', '%'.$request->string('search')->trim()->value().'%'))
            ->orderBy('nama')
            ->paginate(9)
            ->withQueryString();

        return view('public.kawasan.index', compact('kawasans'));
    }

    public function show(int $kawasan): View
    {
        $record = KawasanPerdesaan::query()
            ->established()
            ->with([
                'categories',
                'desas' => fn ($query) => $query->with('kecamatan')->orderBy('nama'),
            ])
            ->findOrFail($kawasan);
        $potentials = PotensiDesa::query()
            ->published()
            ->whereHas('village.kawasans', fn (Builder $query) => $query->whereKey($record->id))
            ->with(['category', 'village', 'coverImage'])
            ->latest('published_at')
            ->latest('id')
            ->paginate(6)
            ->withQueryString();

        return view('public.kawasan.show', compact('record', 'potentials'));
    }
}
