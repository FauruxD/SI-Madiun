<?php

namespace App\Policies;

use App\Models\PotensiDesa;
use App\Models\User;
use App\UserRole;

class PotensiDesaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->desas()->exists())
            || ($user->role === UserRole::AdminKawasan && $user->kawasans()->exists());
    }

    public function view(User $user, PotensiDesa $potential): bool
    {
        return PotensiDesa::query()->visibleTo($user)->whereKey($potential->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->desas()->exists());
    }

    public function update(User $user, PotensiDesa $potential): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $this->view($user, $potential));
    }

    public function delete(User $user, PotensiDesa $potential): bool
    {
        return $this->update($user, $potential) && ! $potential->eproposal()->exists();
    }

    public function manageMedia(User $user, PotensiDesa $potential): bool
    {
        return $this->update($user, $potential);
    }

    public function manageMap(User $user, PotensiDesa $potential): bool
    {
        return $this->view($user, $potential);
    }

    public function restore(User $user, PotensiDesa $potential): bool
    {
        return false;
    }

    public function forceDelete(User $user, PotensiDesa $potential): bool
    {
        return false;
    }
}
