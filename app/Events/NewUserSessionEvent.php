<?php

namespace App\Events;

use App\Http\Resources\SessionResource;
use App\Session;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NewUserSessionEvent
{
    use Dispatchable, SerializesModels;

    public $sessionObj;

    /**
     * Create a new event instance.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->sessionObj = $session;
    }
}
