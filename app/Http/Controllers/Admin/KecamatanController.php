<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKecamatanRequest;
use App\Http\Requests\Admin\UpdateKecamatanRequest;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KecamatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.wilayah.kecamatan.index', [
            'kecamatans' => Kecamatan::query()
                ->with('kabupaten')
                ->withCount('desas')
                ->orderBy('nama')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.wilayah.kecamatan.create', [
            'kabupatens' => Kabupaten::query()->orderBy('nama')->orderBy('id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKecamatanRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $kecamatan = Kecamatan::query()->create($request->validated());
        $recordActivity->execute('wilayah.kecamatan.created', $kecamatan, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kecamatan.index')->with('status', 'Kecamatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kecamatan $kecamatan): View
    {
        return view('admin.wilayah.kecamatan.edit', [
            'kecamatan' => $kecamatan,
            'kabupatens' => Kabupaten::query()->orderBy('nama')->orderBy('id')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKecamatanRequest $request, Kecamatan $kecamatan, RecordActivity $recordActivity): RedirectResponse
    {
        $kecamatan->update($request->validated());
        $recordActivity->execute('wilayah.kecamatan.updated', $kecamatan, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kecamatan.index')->with('status', 'Kecamatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Kecamatan $kecamatan, RecordActivity $recordActivity): RedirectResponse
    {
        Gate::authorize('delete', $kecamatan);

        if ($kecamatan->desas()->exists()) {
            return back()->withErrors(['kecamatan' => 'Kecamatan masih memiliki desa dan tidak dapat dihapus.']);
        }

        $recordActivity->execute('wilayah.kecamatan.deleted', $kecamatan, ['nama' => $kecamatan->nama], $request->user(), $request->ip());
        $kecamatan->delete();

        return back()->with('status', 'Kecamatan berhasil dihapus.');
    }
}
