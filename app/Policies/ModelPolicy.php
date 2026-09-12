<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared authorization for master/operational data: every authenticated
 * user may read; only a non-viewer role (`owner_admin`, `admin`) may write.
 * There is no per-owner scoping — everyone in the company sees the whole
 * fleet (docs/business/discovery.md §L.2 #7). Concrete policies extend this
 * and override only where a model needs a different rule.
 */
abstract class ModelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->canManage();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->role->canManage();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->role->canManage();
    }
}
