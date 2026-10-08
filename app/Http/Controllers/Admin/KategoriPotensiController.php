<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKategoriPotensiRequest;
use App\Http\Requests\Admin\UpdateKategoriPotensiRequest;
use App\Models\KategoriPotensi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KategoriPotensiController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KategoriPotensi::class);

        $categories = KategoriPotensi::query()
            ->withCount('potentials')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(fn ($categories) => $categories
                    ->where('nama', 'like', $search)
                    ->orWhere('kode', 'like', $search));
            })
            ->orderByDesc('is_active')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kategori-potensi.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', KategoriPotensi::class);

        return view('admin.kategori-potensi.create');
    }

    public function store(StoreKategoriPotensiRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $category = KategoriPotensi::query()->create($request->validated());
        $recordActivity->execute('potential.category.created', $category, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kategori-potensi.index')->with('status', 'Kategori potensi berhasil ditambahkan.');
    }

    public function edit(KategoriPotensi $kategoriPotensi): View
    {
        Gate::authorize('update', $kategoriPotensi);

        return view('admin.kategori-potensi.edit', ['category' => $kategoriPotensi]);
    }

    public function update(
        UpdateKategoriPotensiRequest $request,
        KategoriPotensi $kategoriPotensi,
        RecordActivity $recordActivity,
    ): RedirectResponse {
        $kategoriPotensi->update($request->validated());
        $recordActivity->execute('potential.category.updated', $kategoriPotensi, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kategori-potensi.index')->with('status', 'Kategori potensi berhasil diperbarui.');
    }
}
