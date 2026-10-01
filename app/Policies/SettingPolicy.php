<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Setting $setting)
    {
        if ($user->can('setting.index')) {
            return true;
        }
    }

    public function update(User $user, Setting $setting)
    {
        if ($user->can('setting.edit')) {
            return true;
        }
    }
}
