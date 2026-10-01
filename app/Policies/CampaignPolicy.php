<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Campaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('campaign.index')) {
            return true;
        }
    }

    public function view(User $user, Campaign $campaign)
    {
        if ($user->can('campaign.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('campaign.create')) {
            return true;
        }
    }

    public function update(User $user, Campaign $campaign)
    {
        if ($user->can('campaign.edit')) {
            return true;
        }
    }

    public function delete(User $user, Campaign $campaign)
    {
        if ($user->can('campaign.destroy')) {
            return true;
        }
    }

    public function restore(User $user, Campaign $campaign)
    {
        if ($user->can('campaign.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, Campaign $campaign)
    {
        if ($user->can('campaign.forceDelete')) {
            return true;
        }
    }
}
