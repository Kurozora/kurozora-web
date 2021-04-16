<?php

namespace App\Actions\Web;

use App\Contracts\DeletesUsers;
use App\Models\User;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     *
     * @param  User  $user
     * @return void
     */
    public function delete(User $user)
    {
        $user->delete();
    }
}
