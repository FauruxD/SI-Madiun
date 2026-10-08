<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKategoriKawasanRequest;
use App\Http\Requests\Admin\UpdateKategoriKawasanRequest;
use App\Models\KategoriKawasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KategoriKawasanController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KategoriKawasan::class);

        $categories = KategoriKawasan::query()
            ->withCount('kawasans')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(fn ($categories) => $categories
                    ->where('nama', 'like', $search)
                    ->orWhere('kode', 'like', $search));
            })
            ->orderBy('nama')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kategori-kawasan.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        Gate::authorize('create', KategoriKawasan::class);

        return view('admin.kategori-kawasan.create');
    }

    public function store(StoreKategoriKawasanRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $category = KategoriKawasan::query()->create($request->validated());
        $recordActivity->execute('kawasan.category.created', $category, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kategori-kawasan.index')->with('status', 'Kategori kawasan berhasil ditambahkan.');
    }

    public function edit(KategoriKawasan $kategoriKawasan): View
    {
        Gate::authorize('update', $kategoriKawasan);

        return view('admin.kategori-kawasan.edit', ['category' => $kategoriKawasan]);
    }

    public function update(
        UpdateKategoriKawasanRequest $request,
        KategoriKawasan $kategoriKawasan,
        RecordActivity $recordActivity,
    ): RedirectResponse {
        $kategoriKawasan->update($request->validated());
        $recordActivity->execute('kawasan.category.updated', $kategoriKawasan, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kategori-kawasan.index')->with('status', 'Kategori kawasan berhasil diperbarui.');
    }
}
