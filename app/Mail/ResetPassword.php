<?php

namespace App\Mail;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected User $user;
    protected PasswordReset $passwordReset;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param PasswordReset $passwordReset
     */
    public function __construct(User $user, PasswordReset $passwordReset)
    {
        $this->user = $user;
        $this->passwordReset = $passwordReset;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Request to reset your Kurozora password')
            ->view('email.password_reset_notification')
            ->with([
                'title'     => 'Password reset',
                'username'  => $this->user->username,
                'ip'        => $this->passwordReset->ip,
                'reset_url' => route('password.reset', ['token' => $this->passwordReset->token])
            ]);
    }
}
