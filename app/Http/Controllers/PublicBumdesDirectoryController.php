<?php

namespace App\Http\Controllers;

use App\Models\WebsiteBumdes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicBumdesDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $websites = WebsiteBumdes::query()
            ->active()
            ->with(['bumdes.desas' => fn ($query) => $query->with('kecamatan')->orderByPivot('is_desa_utama', 'desc')->orderBy('nama')])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->whereHas('bumdes', function (Builder $bumdes) use ($search): void {
                    $bumdes->where('nama', 'like', $search)
                        ->orWhereHas('desas', fn (Builder $desas) => $desas->where('nama', 'like', $search));
                });
            })
            ->latest('activated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.bumdes.index', compact('websites'));
    }
}
