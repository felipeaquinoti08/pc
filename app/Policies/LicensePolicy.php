<?php

namespace App\Policies;

use App\Models\User;
use App\Models\License;
use Illuminate\Auth\Access\HandlesAuthorization;

class LicensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('license.index')) {
            return true;
        }
    }

    public function view(User $user, License $license)
    {
        if ($user->can('license.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('license.create')) {
            return true;
        }
    }

    public function update(User $user, License $license)
    {
        if ($user->can('license.edit')) {
            return true;
        }
    }

    public function delete(User $user, License $license)
    {
        if ($user->can('license.destroy')) {
            return true;
        }
    }

    public function restore(User $user, License $license)
    {
        if ($user->can('license.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, License $license)
    {
        if ($user->can('license.forceDelete')) {
            return true;
        }
    }
}
