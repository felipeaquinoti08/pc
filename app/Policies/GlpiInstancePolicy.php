<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GlpiInstance;
use Illuminate\Auth\Access\HandlesAuthorization;

class GlpiInstancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('glpi_instance.index')) {
            return true;
        }
    }

    public function view(User $user, GlpiInstance $glpiInstance)
    {
        if ($user->can('glpi_instance.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('glpi_instance.create')) {
            return true;
        }
    }

    public function update(User $user, GlpiInstance $glpiInstance)
    {
        if ($user->can('glpi_instance.edit')) {
            return true;
        }
    }

    public function delete(User $user, GlpiInstance $glpiInstance)
    {
        if ($user->can('glpi_instance.destroy')) {
            return true;
        }
    }

    public function restore(User $user, GlpiInstance $glpiInstance)
    {
        if ($user->can('glpi_instance.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, GlpiInstance $glpiInstance)
    {
        if ($user->can('glpi_instance.forceDelete')) {
            return true;
        }
    }
}
