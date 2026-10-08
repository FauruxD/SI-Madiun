<?php

namespace App\Policies;

use App\Enums\EProposalStatus;
use App\Models\Desa;
use App\Models\EProposal;
use App\Models\PotensiDesa;
use App\Models\User;
use App\UserRole;

class EProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->desas()->exists())
            || ($user->role === UserRole::AdminKawasan && $user->kawasans()->exists());
    }

    public function view(User $user, EProposal $proposal): bool
    {
        return EProposal::query()->visibleTo($user)->whereKey($proposal->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->desas()->exists());
    }

    public function reviewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminKawasan && $user->kawasans()->exists());
    }

    public function review(User $user, EProposal $proposal): bool
    {
        return $this->reviewAny($user) && $this->view($user, $proposal);
    }

    public function resubmit(User $user, EProposal $proposal): bool
    {
        return $proposal->status === EProposalStatus::PerluRevisi
            && ($user->isSuperAdmin() || ($user->role === UserRole::AdminDesa && $this->view($user, $proposal)));
    }

    public function requestRevision(User $user, EProposal $proposal): bool
    {
        return $proposal->status === EProposalStatus::Diajukan
            && ($user->isSuperAdmin() || ($user->role === UserRole::AdminKawasan && $this->view($user, $proposal)));
    }

    public function verify(User $user, EProposal $proposal): bool
    {
        return $proposal->status === EProposalStatus::Diajukan
            && ($user->isSuperAdmin() || ($user->role === UserRole::AdminKawasan && $this->view($user, $proposal)));
    }

    public function publish(User $user, EProposal $proposal): bool
    {
        return $proposal->status === EProposalStatus::Terverifikasi
            && ($user->isSuperAdmin() || ($user->role === UserRole::AdminKawasan && $this->view($user, $proposal)));
    }

    public function download(User $user, EProposal $proposal): bool
    {
        return $proposal->status->isDownloadable()
            && ($user->isSuperAdmin() || ($user->role === UserRole::AdminKawasan && $this->view($user, $proposal)));
    }

    public function submitPotential(User $user, PotensiDesa $potential): bool
    {
        return $this->create($user) && Desa::query()
            ->visibleTo($user)
            ->whereKey($potential->desa_id)
            ->whereHas('potentials', fn ($potentials) => $potentials->whereKey($potential->getKey()))
            ->exists();
    }
}
