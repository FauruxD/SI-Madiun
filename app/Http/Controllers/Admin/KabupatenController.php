<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKabupatenRequest;
use App\Http\Requests\Admin\UpdateKabupatenRequest;
use App\Models\Kabupaten;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KabupatenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.wilayah.kabupaten.index', [
            'kabupatens' => Kabupaten::query()
                ->withCount('kecamatans')
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
        return view('admin.wilayah.kabupaten.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKabupatenRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $kabupaten = Kabupaten::query()->create($request->validated());
        $recordActivity->execute('wilayah.kabupaten.created', $kabupaten, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kabupaten.index')->with('status', 'Kabupaten berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kabupaten $kabupaten): View
    {
        return view('admin.wilayah.kabupaten.edit', compact('kabupaten'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKabupatenRequest $request, Kabupaten $kabupaten, RecordActivity $recordActivity): RedirectResponse
    {
        $kabupaten->update($request->validated());
        $recordActivity->execute('wilayah.kabupaten.updated', $kabupaten, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.kabupaten.index')->with('status', 'Kabupaten berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Kabupaten $kabupaten, RecordActivity $recordActivity): RedirectResponse
    {
        Gate::authorize('delete', $kabupaten);

        if ($kabupaten->kecamatans()->exists()) {
            return back()->withErrors(['kabupaten' => 'Kabupaten masih memiliki kecamatan dan tidak dapat dihapus.']);
        }

        $recordActivity->execute('wilayah.kabupaten.deleted', $kabupaten, ['nama' => $kabupaten->nama], $request->user(), $request->ip());
        $kabupaten->delete();

        return back()->with('status', 'Kabupaten berhasil dihapus.');
    }
}
