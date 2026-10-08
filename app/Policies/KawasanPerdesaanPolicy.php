<?php

namespace App\Policies;

use App\Models\KawasanPerdesaan;
use App\Models\User;
use App\UserRole;

class KawasanPerdesaanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminKawasan && $user->kawasans()->exists());
    }

    public function view(User $user, KawasanPerdesaan $kawasan): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminKawasan
                && $user->kawasans()->whereKey($kawasan->getKey())->exists());
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, KawasanPerdesaan $kawasan): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, KawasanPerdesaan $kawasan): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageDocuments(User $user, KawasanPerdesaan $kawasan): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageMap(User $user, KawasanPerdesaan $kawasan): bool
    {
        return $this->view($user, $kawasan);
    }

    public function restore(User $user, KawasanPerdesaan $kawasan): bool
    {
        return false;
    }

    public function forceDelete(User $user, KawasanPerdesaan $kawasan): bool
    {
        return false;
    }
}
