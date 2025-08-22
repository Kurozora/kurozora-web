<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class Invited
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The invited user.
     */
    public function __construct(
        public User $user,
    )
    {
    }
}

