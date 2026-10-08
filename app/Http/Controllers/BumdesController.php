<?php

namespace App\Http\Controllers;

use App\Actions\CreateBumdesAction;
use App\Actions\RecordActivity;
use App\Actions\UpdateBumdesAction;
use App\Enums\JenisBumdes;
use App\Http\Requests\StoreBumdesRequest;
use App\Http\Requests\UpdateBumdesRequest;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class BumdesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        Gate::authorize('viewAny', Bumdes::class);

        $jenis = $request->string('jenis')->value();
        $allowedJenis = array_column(JenisBumdes::cases(), 'value');
        $desaId = $request->integer('desa');
        $kecamatanId = $request->integer('kecamatan');
        $statusHukum = $request->string('status')->trim()->value();

        $bumdesList = Bumdes::query()
            ->visibleTo($user)
            ->with(['desas' => fn ($query) => $query->with('kecamatan')->orderByPivot('is_desa_utama', 'desc')->orderBy('nama')])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(function ($profiles) use ($search): void {
                    $profiles->where('nama', 'like', $search)
                        ->orWhere('nomor_registrasi', 'like', $search);
                });
            })
            ->when(in_array($jenis, $allowedJenis, true), fn ($query) => $query->where('jenis', $jenis))
            ->when($desaId > 0, fn ($query) => $query->whereHas('desas', fn ($villages) => $villages->whereKey($desaId)))
            ->when($kecamatanId > 0, fn ($query) => $query->whereHas('desas', fn ($villages) => $villages->where('kecamatan_id', $kecamatanId)))
            ->when($statusHukum !== '', fn ($query) => $query->where('status_hukum', $statusHukum))
            ->orderBy('nama')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $visibleDesas = Desa::query()
            ->whereHas('bumdes', fn ($profiles) => $profiles->visibleTo($user))
            ->with('kecamatan')
            ->orderBy('nama')
            ->orderBy('id')
            ->get();

        return view('bumdes.index', [
            'bumdesList' => $bumdesList,
            'jenisOptions' => JenisBumdes::cases(),
            'desas' => $visibleDesas,
            'kecamatans' => Kecamatan::query()
                ->whereKey($visibleDesas->pluck('kecamatan_id')->unique())
                ->orderBy('nama')
                ->orderBy('id')
                ->get(),
            'statusHukumOptions' => Bumdes::query()
                ->visibleTo($user)
                ->whereNotNull('status_hukum')
                ->where('status_hukum', '<>', '')
                ->distinct()
                ->orderBy('status_hukum')
                ->pluck('status_hukum'),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Bumdes::class);

        return view('bumdes.create', [
            'jenisOptions' => JenisBumdes::cases(),
            'desas' => $this->initialDesas((array) $request->old('desa_ids', [])),
        ]);
    }

    public function store(StoreBumdesRequest $request, CreateBumdesAction $createBumdes): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);

        $bumdes = $createBumdes->execute($request->validated(), $actor, $request->ip());

        return redirect()->route('bumdes.show', $bumdes)->with('status', 'Profil BUMDes berhasil dibuat.');
    }

    public function show(Request $request, int $bumdes): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $profile = Bumdes::query()
            ->visibleTo($user)
            ->with(['desas' => fn ($query) => $query->with('kecamatan.kabupaten')->orderByPivot('is_desa_utama', 'desc')->orderBy('nama')])
            ->withCount(['managers', 'businessUnits', 'assets', 'financialSummaries', 'partnerships', 'gallery', 'documents'])
            ->findOrFail($bumdes);
        Gate::authorize('view', $profile);

        $tab = $this->selectedTab($request->string('tab')->value());
        $profile->load($this->relationshipsForTab($tab));

        return view('bumdes.show', ['bumdes' => $profile, 'tab' => $tab]);
    }

    public function edit(Request $request, int $bumdes): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $profile = Bumdes::query()->visibleTo($user)->with('desas.kecamatan.kabupaten')->findOrFail($bumdes);
        Gate::authorize('update', $profile);

        return view('bumdes.edit', [
            'bumdes' => $profile,
            'jenisOptions' => JenisBumdes::cases(),
            'desas' => $user->isSuperAdmin()
                ? $this->initialDesas((array) $request->old('desa_ids', $profile->desas->modelKeys()))
                : collect(),
        ]);
    }

    public function update(
        UpdateBumdesRequest $request,
        int $bumdes,
        UpdateBumdesAction $updateBumdes,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        Gate::authorize('update', $profile);

        $updateBumdes->execute($profile, $request->validated(), $actor, $request->ip());

        return redirect()->route('bumdes.show', $profile)->with('status', 'Profil BUMDes berhasil diperbarui.');
    }

    public function destroy(Request $request, int $bumdes, RecordActivity $recordActivity): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $profile = Bumdes::query()->visibleTo($user)->findOrFail($bumdes);
        Gate::authorize('delete', $profile);

        $recordActivity->execute('bumdes.deleted', $profile, ['nama' => $profile->nama], $user, $request->ip());
        $profile->delete();

        return redirect()->route('bumdes.index')->with('status', 'BUMDes berhasil diarsipkan.');
    }

    /** @return Collection<int, Desa> */
    private function initialDesas(array $selectedIds): Collection
    {
        $ids = collect($selectedIds)->map(fn (int|string $id): int => (int) $id)->unique()->values();
        $selected = Desa::query()->with('kecamatan.kabupaten')->whereKey($ids)->get();
        $additional = Desa::query()
            ->with('kecamatan.kabupaten')
            ->when($ids->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $ids))
            ->orderBy('nama')
            ->orderBy('id')
            ->limit(30)
            ->get();

        return $selected->concat($additional)->unique('id')->sortBy([['nama', 'asc'], ['id', 'asc']])->values();
    }

    private function selectedTab(string $tab): string
    {
        $allowed = ['profil', 'organisasi', 'unit-usaha', 'produk', 'aset', 'keuangan', 'kemitraan', 'galeri', 'dokumen'];

        return in_array($tab, $allowed, true) ? $tab : 'profil';
    }

    /** @return array<int, string> */
    private function relationshipsForTab(string $tab): array
    {
        return match ($tab) {
            'organisasi' => ['managers' => fn ($query) => $query->orderBy('urutan')->orderBy('id')],
            'unit-usaha' => ['businessUnits' => fn ($query) => $query->orderBy('nama')->orderBy('id')],
            'produk' => ['businessUnits' => fn ($query) => $query->with(['products' => fn ($products) => $products->orderBy('nama')->orderBy('id')])->orderBy('nama')],
            'aset' => ['assets' => fn ($query) => $query->orderBy('jenis')->orderBy('nama')],
            'keuangan' => ['financialSummaries' => fn ($query) => $query->orderByDesc('tahun')],
            'kemitraan' => ['partnerships' => fn ($query) => $query->orderBy('status')->orderBy('nama_mitra')],
            'galeri' => ['gallery' => fn ($query) => $query->with('uploader')->orderBy('urutan')->orderBy('id')],
            'dokumen' => ['documents' => fn ($query) => $query->with('uploader')->latest()],
            default => [],
        };
    }
}
