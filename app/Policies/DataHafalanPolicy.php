<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DataHafalan;

class DataHafalanPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, DataHafalan $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DataHafalan $model): bool
    {
        return false;
    }

    public function delete(User $user, DataHafalan $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
