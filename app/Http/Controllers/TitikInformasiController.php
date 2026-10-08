<?php

namespace App\Http\Controllers;

use App\Actions\SaveTitikInformasi;
use App\Http\Requests\SaveTitikInformasiRequest;
use App\Models\TitikInformasi;
use App\Services\MapEditorScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TitikInformasiController extends Controller
{
    public function create(Request $request, MapEditorScope $scope, int $kawasan): View
    {
        return view('maps.information', ['kawasan' => $scope->find('kawasan', $kawasan, $request->user()), 'point' => new TitikInformasi, 'geometryType' => 'Point']);
    }

    public function edit(Request $request, MapEditorScope $scope, int $kawasan, int $point): View
    {
        $parent = $scope->find('kawasan', $kawasan, $request->user());

        return view('maps.information', ['kawasan' => $parent, 'point' => $parent->informationPoints()->findOrFail($point), 'geometryType' => 'Point']);
    }

    public function store(SaveTitikInformasiRequest $request, MapEditorScope $scope, SaveTitikInformasi $save, int $kawasan): RedirectResponse
    {
        return $this->save($request, $scope, $save, $kawasan, null);
    }

    public function update(SaveTitikInformasiRequest $request, MapEditorScope $scope, SaveTitikInformasi $save, int $kawasan, int $point): RedirectResponse
    {
        return $this->save($request, $scope, $save, $kawasan, $point);
    }

    private function save(SaveTitikInformasiRequest $request, MapEditorScope $scope, SaveTitikInformasi $save, int $kawasan, ?int $point): RedirectResponse
    {
        $parent = $scope->find('kawasan', $kawasan, $request->user());
        $save->execute($parent, $point, $request->validated(), $request->user(), $request->ip());

        return to_route('peta.geometry.edit', ['kawasan', $kawasan])->with('status', 'Titik informasi berhasil disimpan.');
    }
}
