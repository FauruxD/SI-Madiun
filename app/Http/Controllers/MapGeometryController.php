<?php

namespace App\Http\Controllers;

use App\Actions\SaveMapGeometry;
use App\Http\Requests\UpdateMapGeometryRequest;
use App\Services\MapEditorScope;
use App\Services\MapFeatureService;
use App\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MapGeometryController extends Controller
{
    public function index(Request $request, MapEditorScope $scope, MapFeatureService $features): View
    {
        $validated = $request->validate(['kind' => ['sometimes', Rule::in(array_keys(MapEditorScope::KINDS))], 'q' => ['nullable', 'string', 'max:120']]);
        $kind = $validated['kind'] ?? ($request->user()->isSuperAdmin() || $request->user()->role === UserRole::AdminKawasan ? 'kawasan' : 'bumdes');
        $query = $scope->query($kind, $request->user());
        if (! empty($validated['q'])) {
            $query->where($kind === 'potensi' ? 'judul' : 'nama', 'like', '%'.addcslashes($validated['q'], '%_\\').'%');
        }

        return view('maps.manage', [
            'kind' => $kind, 'kinds' => MapEditorScope::KINDS, 'entities' => $query->orderBy('id')->paginate(15)->withQueryString(),
            'collection' => $features->collection($request->user()),
        ]);
    }

    public function data(Request $request, MapFeatureService $features): JsonResponse
    {
        return response()->json($features->collection($request->user()))->header('Cache-Control', 'private, no-store');
    }

    public function edit(Request $request, MapEditorScope $scope, string $kind, int $id): View
    {
        $entity = $scope->find($kind, $id, $request->user());

        return view('maps.edit', [
            'kind' => $kind, 'entity' => $entity, 'geometryType' => $kind === 'kawasan' ? 'Polygon' : 'Point',
            'informationPoints' => $kind === 'kawasan' ? $entity->informationPoints()->orderBy('nama')->paginate(15) : null,
        ]);
    }

    public function update(UpdateMapGeometryRequest $request, MapEditorScope $scope, SaveMapGeometry $save, string $kind, int $id): RedirectResponse
    {
        $entity = $scope->find($kind, $id, $request->user());
        $save->execute($entity, $request->validated('geometri'), $request->user(), $request->ip());

        return to_route('peta.geometry.edit', [$kind, $id])->with('status', 'Lokasi peta berhasil disimpan.');
    }
}
