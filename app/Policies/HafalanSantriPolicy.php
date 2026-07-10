<?php

namespace App\Policies;

use App\Models\User;
use App\Models\HafalanSantri;

class HafalanSantriPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('inputer');
    }

    public function view(User $user, HafalanSantri $model): bool
    {
        return $user->hasRole('inputer');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('inputer');
    }

    public function update(User $user, HafalanSantri $model): bool
    {
        return $user->hasRole('inputer');
    }

    public function delete(User $user, HafalanSantri $model): bool
    {
        return $user->hasRole('inputer');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('inputer');
    }
}
