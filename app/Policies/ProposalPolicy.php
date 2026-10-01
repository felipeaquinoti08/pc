<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('proposal.index')) {
            return true;
        }
    }

    public function view(User $user, Proposal $proposal)
    {
        if ($user->can('proposal.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('proposal.create')) {
            return true;
        }
    }

    public function update(User $user, Proposal $proposal)
    {
        if ($user->can('proposal.edit')) {
            return true;
        }
    }

    public function delete(User $user, Proposal $proposal)
    {
        if ($user->can('proposal.destroy')) {
            return true;
        }
    }

    public function restore(User $user, Proposal $proposal)
    {
        if ($user->can('proposal.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, Proposal $proposal)
    {
        if ($user->can('proposal.forceDelete')) {
            return true;
        }
    }
}
