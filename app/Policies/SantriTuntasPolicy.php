<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SantriTuntas;

class SantriTuntasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'inputer']);
    }

    public function view(User $user, SantriTuntas $model): bool
    {
        return $user->hasAnyRole(['admin', 'inputer']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SantriTuntas $model): bool
    {
        return false;
    }

    public function delete(User $user, SantriTuntas $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
