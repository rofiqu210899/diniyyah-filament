<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mustahiq;

class MustahiqPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Mustahiq $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Mustahiq $model): bool
    {
        return false;
    }

    public function delete(User $user, Mustahiq $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
