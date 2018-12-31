<?php

namespace App\Events;

use App\Helpers\KuroMail;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewUserRegisteredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @throws \Throwable
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $this->sendEmailConfirmation();
    }

    /**
     * Sends an email confirmation mail to the user
     *
     * @return bool
     * @throws \Throwable
     */
    public function sendEmailConfirmation() {
        // Create email data
        $emailData = [
            'title'             => 'Your Kurozora account registration',
            'username'          => $this->user->username,
            'confirmation_url'  => url('/confirmation/' . $this->user->email_confirmation_id)
        ];

        // Send email
        (new KuroMail())
            ->setTo($this->user->email)
            ->setSubject($emailData['title'])
            ->setContent(
                view('email.confirmation_email', $emailData)->render()
            )
            ->send();

        return true;
    }
}
