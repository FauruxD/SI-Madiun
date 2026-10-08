<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateKawasanAction;
use App\Actions\RecordActivity;
use App\Actions\UpdateKawasanAction;
use App\Enums\KawasanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKawasanRequest;
use App\Http\Requests\Admin\UpdateKawasanRequest;
use App\Models\Desa;
use App\Models\KategoriKawasan;
use App\Models\KawasanPerdesaan;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class KawasanController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KawasanPerdesaan::class);

        $categoryId = $request->integer('category');
        $allowedStatuses = array_column(KawasanStatus::cases(), 'value');
        $status = $request->string('status')->value();

        $kawasans = KawasanPerdesaan::query()
            ->with(['categories:id,nama', 'adminUsers:id,name'])
            ->withCount('desas')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where('nama', 'like', $search);
            })
            ->when($categoryId > 0, fn ($query) => $query->whereHas(
                'categories',
                fn ($categories) => $categories->whereKey($categoryId),
            ))
            ->when(in_array($status, $allowedStatuses, true), fn ($query) => $query->where('status', $status))
            ->orderBy('nama')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kawasan.index', [
            'kawasans' => $kawasans,
            'categories' => KategoriKawasan::query()->orderBy('nama')->orderBy('id')->get(),
            'statuses' => KawasanStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', KawasanPerdesaan::class);

        return view('admin.kawasan.create', $this->formOptions(
            oldDesaIds: (array) $request->old('desa_ids', []),
        ));
    }

    public function store(StoreKawasanRequest $request, CreateKawasanAction $createKawasan): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);

        $kawasan = $createKawasan->execute($request->validated(), $actor, $request->ip());

        return redirect()->route('admin.kawasan.show', $kawasan)->with('status', 'Kawasan berhasil dibuat.');
    }

    public function show(KawasanPerdesaan $kawasan): View
    {
        Gate::authorize('view', $kawasan);

        return view('admin.kawasan.show', [
            'kawasan' => $kawasan->load([
                'categories',
                'desas.kecamatan.kabupaten',
                'adminUsers',
                'documents.uploader',
            ]),
        ]);
    }

    public function edit(Request $request, KawasanPerdesaan $kawasan): View
    {
        Gate::authorize('update', $kawasan);
        $kawasan->load(['categories', 'desas', 'adminUsers']);

        return view('admin.kawasan.edit', [
            'kawasan' => $kawasan,
            ...$this->formOptions($kawasan, (array) $request->old('desa_ids', [])),
        ]);
    }

    public function update(
        UpdateKawasanRequest $request,
        KawasanPerdesaan $kawasan,
        UpdateKawasanAction $updateKawasan,
    ): RedirectResponse {
        $actor = $request->user();
        abort_if($actor === null, 401);

        $updateKawasan->execute($kawasan, $request->validated(), $actor, $request->ip());

        return redirect()->route('admin.kawasan.show', $kawasan)->with('status', 'Kawasan berhasil diperbarui.');
    }

    public function destroy(Request $request, KawasanPerdesaan $kawasan, RecordActivity $recordActivity): RedirectResponse
    {
        Gate::authorize('delete', $kawasan);

        $recordActivity->execute('kawasan.deleted', $kawasan, ['nama' => $kawasan->nama], $request->user(), $request->ip());
        $kawasan->delete();

        return redirect()->route('admin.kawasan.index')->with('status', 'Kawasan berhasil dihapus secara aman.');
    }

    /** @return array<string, mixed> */
    private function formOptions(?KawasanPerdesaan $kawasan = null, array $oldDesaIds = []): array
    {
        $selectedCategoryIds = $kawasan?->categories->modelKeys() ?? [];
        $selectedDesaIds = $oldDesaIds !== [] ? $oldDesaIds : ($kawasan?->desas->modelKeys() ?? []);

        return [
            'statuses' => KawasanStatus::cases(),
            'categories' => KategoriKawasan::query()
                ->where(function ($categories) use ($selectedCategoryIds): void {
                    $categories->where('is_active', true)
                        ->when($selectedCategoryIds !== [], fn ($query) => $query->orWhereIn('id', $selectedCategoryIds));
                })
                ->orderBy('nama')
                ->orderBy('id')
                ->get(),
            'desas' => $this->initialDesas($selectedDesaIds),
            'adminUsers' => User::query()
                ->where('role', UserRole::AdminKawasan)
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
        ];
    }

    /**
     * @param  array<int, int|string>  $selectedIds
     * @return Collection<int, Desa>
     */
    private function initialDesas(array $selectedIds): Collection
    {
        $ids = collect($selectedIds)->map(fn (int|string $id): int => (int) $id)->unique()->values();
        $selectedDesas = Desa::query()
            ->with('kecamatan.kabupaten')
            ->whereKey($ids)
            ->get();
        $additionalDesas = Desa::query()
            ->with('kecamatan.kabupaten')
            ->when($ids->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $ids))
            ->orderBy('nama')
            ->orderBy('id')
            ->limit(30)
            ->get();

        return $selectedDesas
            ->concat($additionalDesas)
            ->unique('id')
            ->sortBy([['nama', 'asc'], ['id', 'asc']])
            ->values();
    }
}
