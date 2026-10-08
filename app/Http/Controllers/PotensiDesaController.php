<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Actions\SavePotensiDesaAction;
use App\Enums\PotensiStatus;
use App\Http\Requests\StorePotensiDesaRequest;
use App\Http\Requests\UpdatePotensiDesaRequest;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\KategoriPotensi;
use App\Models\PotensiDesa;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PotensiDesaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        Gate::authorize('viewAny', PotensiDesa::class);

        $categoryId = $request->integer('kategori');
        $status = PotensiStatus::tryFrom($request->string('status')->value());
        $potentials = PotensiDesa::query()
            ->visibleTo($user)
            ->with(['village.kecamatan', 'category', 'bumdes'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(function (Builder $potentials) use ($search): void {
                    $potentials->where('judul', 'like', $search)
                        ->orWhereHas('village', fn (Builder $villages) => $villages->where('nama', 'like', $search));
                });
            })
            ->when($categoryId > 0, fn (Builder $query) => $query->where('kategori_potensi_id', $categoryId))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status->value))
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('potensi-desa.index', [
            'potentials' => $potentials,
            'categories' => KategoriPotensi::query()->orderBy('nama')->get(),
            'statuses' => PotensiStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        Gate::authorize('create', PotensiDesa::class);

        return view('potensi-desa.create', [
            'villages' => $this->manageableVillages($user),
            'categories' => KategoriPotensi::query()->where('is_active', true)->orderBy('nama')->get(),
            'bumdesOptions' => $this->manageableBumdes($user),
            'statuses' => PotensiStatus::cases(),
        ]);
    }

    public function store(StorePotensiDesaRequest $request, SavePotensiDesaAction $savePotential): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $potential = $savePotential->execute($request->validated(), $actor, ipAddress: $request->ip());

        return redirect()->route('potensi-desa.show', $potential)->with('status', 'Potensi desa berhasil dibuat.');
    }

    public function show(Request $request, int $potential): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()
            ->visibleTo($actor)
            ->with([
                'village.kecamatan.kabupaten',
                'village.kawasans',
                'category',
                'bumdes',
                'media' => fn ($query) => $query->with('uploader')->orderBy('urutan')->orderBy('id'),
            ])
            ->findOrFail($potential);
        Gate::authorize('view', $record);

        return view('potensi-desa.show', ['potential' => $record]);
    }

    public function edit(Request $request, int $potential): View
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->with('eproposal')->findOrFail($potential);
        Gate::authorize('update', $record);

        return view('potensi-desa.edit', [
            'potential' => $record,
            'villages' => $this->manageableVillages($actor),
            'categories' => KategoriPotensi::query()
                ->where('is_active', true)
                ->orWhereKey($record->kategori_potensi_id)
                ->orderBy('nama')
                ->get(),
            'bumdesOptions' => $this->manageableBumdes($actor),
            'statuses' => PotensiStatus::cases(),
        ]);
    }

    public function update(
        UpdatePotensiDesaRequest $request,
        int $potential,
        SavePotensiDesaAction $savePotential,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->findOrFail($potential);
        Gate::authorize('update', $record);
        $savePotential->execute($request->validated(), $actor, $record, $request->ip());

        return redirect()->route('potensi-desa.show', $record)->with('status', 'Potensi desa berhasil diperbarui.');
    }

    public function destroy(Request $request, int $potential, RecordActivity $recordActivity): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = PotensiDesa::query()->visibleTo($actor)->findOrFail($potential);
        Gate::authorize('delete', $record);

        DB::transaction(function () use ($record, $recordActivity, $actor, $request): void {
            $record = PotensiDesa::query()->lockForUpdate()->findOrFail($record->getKey());
            Gate::authorize('delete', $record);
            $recordActivity->execute(
                'potential.deleted',
                $record,
                ['desa_id' => $record->desa_id, 'status' => $record->status->value],
                $actor,
                $request->ip(),
            );
            $record->delete();
        });

        return redirect()->route('potensi-desa.index')->with('status', 'Potensi desa berhasil diarsipkan.');
    }

    /** @return Collection<int, Desa> */
    private function manageableVillages(User $user): Collection
    {
        return Desa::query()
            ->visibleTo($user)
            ->with('kecamatan')
            ->orderBy('nama')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, Bumdes> */
    private function manageableBumdes(User $user): Collection
    {
        return Bumdes::query()
            ->visibleTo($user)
            ->with(['desas' => fn ($query) => $query->orderBy('nama')])
            ->orderBy('nama')
            ->orderBy('id')
            ->get();
    }
}
