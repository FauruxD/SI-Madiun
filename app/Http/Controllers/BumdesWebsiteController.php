<?php

namespace App\Http\Controllers;

use App\Actions\SaveBumdesWebsiteAction;
use App\Http\Requests\UpdateBumdesWebsiteRequest;
use App\Models\Bumdes;
use App\Models\WebsiteBumdes;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BumdesWebsiteController extends Controller
{
    public function edit(Request $request, int $bumdes): View
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $profile = Bumdes::query()->visibleTo($user)->with('website')->findOrFail($bumdes);
        Gate::authorize('manageWebsite', $profile);

        return view('bumdes.website', [
            'bumdes' => $profile,
            'website' => $profile->website ?? new WebsiteBumdes(['subdomain' => WebsiteBumdes::defaultSubdomain($profile), 'is_active' => false]),
        ]);
    }

    public function update(UpdateBumdesWebsiteRequest $request, int $bumdes, SaveBumdesWebsiteAction $saveWebsite): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $profile = Bumdes::query()->visibleTo($actor)->findOrFail($bumdes);
        $saveWebsite->execute($profile, $request->validated(), $actor, $request->ip());

        return redirect()->route('bumdes.website.edit', $profile)->with('status', 'Pengaturan website BUMDes berhasil disimpan.');
    }
}
