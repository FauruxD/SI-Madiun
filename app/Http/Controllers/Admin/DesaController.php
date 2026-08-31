<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDesaRequest;
use App\Http\Requests\Admin\UpdateDesaRequest;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.wilayah.desa.index', [
            'desas' => Desa::query()->with('kecamatan.kabupaten')->withCount('users')->orderBy('nama')->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.wilayah.desa.create', [
            'kecamatans' => Kecamatan::query()->with('kabupaten')->orderBy('nama')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDesaRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $desa = Desa::query()->create($request->validated());
        $recordActivity->execute('wilayah.desa.created', $desa, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.desa.index')->with('status', 'Desa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $desa): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $visibleDesa = Desa::query()
            ->visibleTo($user)
            ->with('kecamatan.kabupaten')
            ->findOrFail($desa);

        return view('admin.wilayah.desa.show', ['desa' => $visibleDesa]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Desa $desa): View
    {
        return view('admin.wilayah.desa.edit', [
            'desa' => $desa,
            'kecamatans' => Kecamatan::query()->with('kabupaten')->orderBy('nama')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDesaRequest $request, Desa $desa, RecordActivity $recordActivity): RedirectResponse
    {
        $desa->update($request->validated());
        $recordActivity->execute('wilayah.desa.updated', $desa, user: $request->user(), ipAddress: $request->ip());

        return redirect()->route('admin.desa.index')->with('status', 'Desa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Desa $desa, RecordActivity $recordActivity): RedirectResponse
    {
        Gate::authorize('delete', $desa);

        if ($desa->users()->exists()) {
            return back()->withErrors(['desa' => 'Desa masih ditugaskan kepada pengguna dan tidak dapat dihapus.']);
        }

        $recordActivity->execute('wilayah.desa.deleted', $desa, ['nama' => $desa->nama], $request->user(), $request->ip());
        $desa->delete();

        return back()->with('status', 'Desa berhasil dihapus.');
    }
}
