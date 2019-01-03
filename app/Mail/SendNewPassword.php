<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $newPassword;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param $newPassword
     */
    public function __construct(User $user, $newPassword)
    {
        $this->user = $user;
        $this->newPassword = $newPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
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
