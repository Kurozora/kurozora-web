<?php

namespace App\Observers;

use App\User;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Called when a User is deleted
     *
     * @param User $user
     */
    public function deleted(User $user)
    {
        // Delete the user's avatar
        if($user->hasAvatar()) {
            $avatarPath = $user->getAvatarPath();

            if(Storage::exists($avatarPath))
                Storage::delete($avatarPath);
        }
    }
}
