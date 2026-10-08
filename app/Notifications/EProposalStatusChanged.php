<?php

namespace App\Notifications;

use App\Enums\EProposalStatus;
use App\Models\EProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EProposalStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public EProposal $proposal, public EProposalStatus $status, public string $message) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'message' => $this->message,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'eproposal_id' => $this->proposal->getKey(),
            'potensi_desa_id' => $this->proposal->potensi_desa_id,
            'url' => route('admin.eproposal.show', $this->proposal),
        ];
    }
}
