<?php

namespace App\Policies;

use App\Models\Bumdes;
use App\Models\RencanaAksi;
use App\Models\User;
use App\UserRole;

class RencanaAksiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('viewAny', Bumdes::class);
    }

    public function view(User $user, RencanaAksi $actionPlan): bool
    {
        return RencanaAksi::query()->visibleTo($user)->whereKey($actionPlan->getKey())->exists();
    }

    public function create(User $user, Bumdes $bumdes): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $user->can('view', $bumdes));
    }

    public function update(User $user, RencanaAksi $actionPlan): bool
    {
        return $user->isSuperAdmin()
            || ($user->role === UserRole::AdminDesa && $this->view($user, $actionPlan));
    }

    public function delete(User $user, RencanaAksi $actionPlan): bool
    {
        return $this->update($user, $actionPlan);
    }

    public function manageRealizations(User $user, RencanaAksi $actionPlan): bool
    {
        return $this->update($user, $actionPlan);
    }
}
