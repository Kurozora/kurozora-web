<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user to whom the new password will be sent.
     *
     * @var User
     */
    protected User $user;

    /**
     * The new password of the user.
     *
     * @var string
     */
    protected string $newPassword;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $newPassword
     */
    public function __construct(User $user, string $newPassword)
    {
        $this->user = $user;
        $this->newPassword = $newPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->subject('Your new Kurozora password')
            ->view('email.password_reset_new_pass')
            ->with([
                'title'     => 'Your new password',
                'username'  => $this->user->username,
                'newPass'   => $this->newPassword
            ]);
    }
}
