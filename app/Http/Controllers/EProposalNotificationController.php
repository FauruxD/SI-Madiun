<?php

namespace App\Http\Controllers;

use App\Notifications\EProposalStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EProposalNotificationController extends Controller
{
    public function update(Request $request, string $notification): RedirectResponse
    {
        $actor = $request->user();
        abort_if($actor === null, 401);
        $record = $actor->notifications()
            ->where('type', EProposalStatusChanged::class)
            ->findOrFail($notification);
        $record->markAsRead();

        return redirect()->route('admin.eproposal.index')->with('status', 'Notifikasi ditandai sudah dibaca.');
    }
}
