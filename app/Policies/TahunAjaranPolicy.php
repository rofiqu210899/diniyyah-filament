<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TahunAjaran;

class TahunAjaranPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, TahunAjaran $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TahunAjaran $model): bool
    {
        return false;
    }

    public function delete(User $user, TahunAjaran $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
