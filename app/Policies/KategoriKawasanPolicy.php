<?php

namespace App\Policies;

use App\Models\KategoriKawasan;
use App\Models\User;

class KategoriKawasanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, KategoriKawasan $kategori): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, KategoriKawasan $kategori): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, KategoriKawasan $kategori): bool
    {
        return false;
    }
}
