<?php

namespace App\Policies;

use App\Models\KategoriPotensi;
use App\Models\User;

class KategoriPotensiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, KategoriPotensi $category): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, KategoriPotensi $category): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, KategoriPotensi $category): bool
    {
        return false;
    }
}
