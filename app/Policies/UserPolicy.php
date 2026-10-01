<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Same read-all/non-viewer-writes rule as every other ModelPolicy, with one
 * addition: nobody can delete their own account from this screen, so an
 * admin can never accidentally lock themselves out.
 */
class UserPolicy extends ModelPolicy
{
    public function delete(User $user, Model $model): bool
    {
        return parent::delete($user, $model) && $user->isNot($model);
    }
}
