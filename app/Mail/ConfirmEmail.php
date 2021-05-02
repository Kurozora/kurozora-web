<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User $user
     */
    protected User $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->subject('Please confirm your email address')
            ->view('email.confirmation_email')
            ->with([
                'title'             => 'Your Kurozora account registration',
                'username'          => $this->user->username,
                'verification_url'  => route('verification.verify')
            ]);
    }
}
