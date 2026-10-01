<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('customer.index')) {
            return true;
        }
    }

    public function view(User $user, Customer $customer)
    {
        if ($user->can('customer.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('customer.create')) {
            return true;
        }
    }

    public function update(User $user, Customer $customer)
    {
        if ($user->can('customer.edit')) {
            return true;
        }
    }

    public function delete(User $user, Customer $customer)
    {
        if ($user->can('customer.destroy')) {
            return true;
        }
    }

    public function restore(User $user, Customer $customer)
    {
        if ($user->can('customer.restore')) {
            return true;
        }
    }

    public function forceDelete(User $user, Customer $customer)
    {
        if ($user->can('customer.forceDelete')) {
            return true;
        }
    }
}
