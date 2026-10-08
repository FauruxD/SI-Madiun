<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KawasanOptionController extends Controller
{
    public function desas(Request $request): JsonResponse
    {
        $selectedIds = collect($request->input('selected', []))
            ->filter(fn (mixed $id): bool => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->take(200)
            ->values();
        $search = $request->string('search')->trim()->limit(100, '')->value();

        $selectedDesas = Desa::query()
            ->with('kecamatan.kabupaten')
            ->whereKey($selectedIds)
            ->get();

        $matchingDesas = Desa::query()
            ->with('kecamatan.kabupaten')
            ->when($selectedIds->isNotEmpty(), fn (Builder $query): Builder => $query->whereNotIn('id', $selectedIds))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';
                $query->where(function (Builder $desas) use ($pattern): void {
                    $desas->where('nama', 'like', $pattern)
                        ->orWhere('kode', 'like', $pattern)
                        ->orWhereHas('kecamatan', fn (Builder $kecamatans) => $kecamatans->where('nama', 'like', $pattern));
                });
            })
            ->orderBy('nama')
            ->orderBy('id')
            ->limit(30)
            ->get();

        $desas = $selectedDesas
            ->concat($matchingDesas)
            ->unique('id')
            ->sortBy([['nama', 'asc'], ['id', 'asc']])
            ->values();

        return response()->json([
            'data' => $desas->map(fn (Desa $desa): array => [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'kode' => $desa->kode,
                'kecamatan' => $desa->kecamatan->nama,
                'kabupaten' => $desa->kecamatan->kabupaten->nama,
            ]),
        ]);
    }
}
