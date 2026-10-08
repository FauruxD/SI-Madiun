<?php

namespace App\Policies;

use App\Models\Bumdes;
use App\Models\User;
use App\UserRole;

class BumdesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->desas()->exists())
            || ($user->role === UserRole::AdminKawasan && $user->kawasans()->exists());
    }

    public function view(User $user, Bumdes $bumdes): bool
    {
        return Bumdes::query()->visibleTo($user)->whereKey($bumdes->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Bumdes $bumdes): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $this->view($user, $bumdes));
    }

    public function delete(User $user, Bumdes $bumdes): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageData(User $user, Bumdes $bumdes): bool
    {
        return $this->update($user, $bumdes);
    }

    public function manageMap(User $user, Bumdes $bumdes): bool
    {
        return $this->view($user, $bumdes);
    }

    public function manageWebsite(User $user, Bumdes $bumdes): bool
    {
        return $this->update($user, $bumdes);
    }

    public function downloadCompanyProfile(User $user, Bumdes $bumdes): bool
    {
        return $this->update($user, $bumdes);
    }

    public function manageDocuments(User $user, Bumdes $bumdes): bool
    {
        return $this->manageData($user, $bumdes);
    }

    public function restore(User $user, Bumdes $bumdes): bool
    {
        return false;
    }

    public function forceDelete(User $user, Bumdes $bumdes): bool
    {
        return false;
    }
}
