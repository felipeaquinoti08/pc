<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BillingPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillingPolicyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('billing_policy.index')) {
            return true;
        }
    }

    public function view(User $user, BillingPolicy $billingPolicy)
    {
        if ($user->can('billing_policy.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('billing_policy.create')) {
            return true;
        }
    }

    public function update(User $user, BillingPolicy $billingPolicy)
    {
        if ($user->can('billing_policy.edit')) {
            return true;
        }
    }

    public function delete(User $user, BillingPolicy $billingPolicy)
    {
        if ($user->can('billing_policy.destroy')) {
            return true;
        }
    }

    public function restore(User $user, BillingPolicy $billingPolicy)
    {
        if ($user->can('billing_policy.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, BillingPolicy $billingPolicy)
    {
        if ($user->can('billing_policy.forceDelete')) {
            return true;
        }
    }
}
