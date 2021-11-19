<?php

namespace App\Events;

use App\Models\UserLibrary;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLibraryUpdatedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The user whose MAL import has finished.
     *
     * @var UserLibrary
     */
    public UserLibrary $userLibrary;

    /**
     * Create a new event instance.
     *
     * @param UserLibrary $userLibrary
     */
    public function __construct(UserLibrary $userLibrary)
    {
        $this->userLibrary = $userLibrary;
    }
}
