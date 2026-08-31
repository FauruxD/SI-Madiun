<?php

namespace App\Listeners;

use App\Actions\RecordActivity;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly RecordActivity $recordActivity) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->recordActivity->execute(
            action: 'auth.login',
            subject: $event->user,
            metadata: ['guard' => $event->guard, 'remember' => $event->remember],
            user: $event->user,
            ipAddress: request()->ip(),
        );
    }
}
