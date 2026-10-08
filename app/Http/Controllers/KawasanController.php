<?php

namespace App\Http\Controllers;

use App\Models\KawasanPerdesaan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KawasanController extends Controller
{
    public function show(Request $request, int $kawasan): View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $visibleKawasan = KawasanPerdesaan::query()
            ->visibleTo($user)
            ->with(['categories', 'desas.kecamatan.kabupaten', 'adminUsers', 'documents.uploader'])
            ->findOrFail($kawasan);

        Gate::authorize('view', $visibleKawasan);

        return view('admin.kawasan.show', ['kawasan' => $visibleKawasan]);
    }
}
