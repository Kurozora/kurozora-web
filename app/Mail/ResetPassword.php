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

    /**
     * The user receiving the password reset email.
     *
     * @var User $user
     */
    protected User $user;

    /**
     * The password reset object.
     *
     * @var PasswordReset $passwordReset
     */
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
    public function build(): static
    {
        return $this
            ->subject('Request to reset your Kurozora password')
            ->view('email.password_reset_notification')
            ->with([
                'title'         => 'Password reset',
                'username'      => $this->user->username,
                'ip_address'    => $this->passwordReset->ip_address,
                'reset_url'     => route('password.reset', ['token' => $this->passwordReset->token])
            ]);
    }
}
