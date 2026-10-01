<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContactLead;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactLeadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('contact_lead.index')) {
            return true;
        }
    }

    public function view(User $user, ContactLead $contactLead)
    {
        if ($user->can('contact_lead.index')) {
            return true;
        }
    }

    public function update(User $user, ContactLead $contactLead)
    {
        if ($user->can('contact_lead.edit')) {
            return true;
        }
    }

    public function delete(User $user, ContactLead $contactLead)
    {
        if ($user->can('contact_lead.destroy')) {
            return true;
        }
    }
}
