<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

class UserProfilePolicy
{
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function view(User $user, ?UserProfile $profile)
    {
        if (!$profile) {
            return $user->isAdmin();
        }

        return $user->isAdmin() || $user->id === $profile->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, ?UserProfile $profile)
    {
        if (!$profile) {
            return true; // Allow creating new profile
        }

        return $user->isAdmin() || $user->id === $profile->user_id;
    }

    public function delete(User $user, UserProfile $profile)
    {
        return $user->isAdmin();
    }
}
