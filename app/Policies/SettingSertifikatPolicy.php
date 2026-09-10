<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SettingSertifikat;

class SettingSertifikatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, SettingSertifikat $model): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, SettingSertifikat $model): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, SettingSertifikat $model): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
