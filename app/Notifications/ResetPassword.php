<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url): MailMessage
    {
        $expirationDuration = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $expirationDuration = $expirationDuration / 60;

        return (new MailMessage)
            ->subject(Lang::get('Reset Your Kurozora ID Password'))
            ->line(Lang::get('You recently made a request to reset your Kurozora ID. Please click the button below to complete the process.'))
            ->line(Lang::get('This password reset link will expire in :count hours.', [
                'count' => $expirationDuration
            ]))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('If you did not request a password reset, it’s likely that another user has entered your email address by mistake and your account is still secure. If you believe an unauthorized person has accessed your account, you can reset your password at kurozora.app.'))
            ->salutation('Kurozora Support');
    }
}
