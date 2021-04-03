<?php

namespace App\Events;

use App\Jobs\SendEmailConfirmationMail;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewUserRegisteredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @throws \Throwable
     */
    public function __construct(User $user)
    {
        // Dispatch job to send confirmation mail
        SendEmailConfirmationMail::dispatch($user);
    }
}
