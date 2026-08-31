<?php

namespace App\Http\Controllers;

use App\Actions\RecordActivity;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(ProfileUpdateRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $changed = array_keys($user->getDirty());
        $user->save();

        $recordActivity->execute(
            'profile.updated',
            $user,
            ['changed_fields' => $changed],
            $user,
            $request->ip(),
        );

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
