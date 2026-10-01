<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('service.index')) {
            return true;
        }
    }

    public function view(User $user, Service $service)
    {
        if ($user->can('service.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('service.create')) {
            return true;
        }
    }

    public function update(User $user, Service $service)
    {
        if ($user->can('service.edit')) {
            return true;
        }
    }

    public function delete(User $user, Service $service)
    {
        if ($user->can('service.destroy')) {
            return true;
        }
    }

    public function restore(User $user, Service $service)
    {
        if ($user->can('service.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, Service $service)
    {
        if ($user->can('service.forceDelete')) {
            return true;
        }
    }
}
