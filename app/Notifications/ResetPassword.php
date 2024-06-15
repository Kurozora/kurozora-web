<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

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
            ->subject(__('Reset your Kurozora Account password'))
            ->line(__('You recently made a request to reset your Kurozora Account. Please click the button below to complete the process.'))
            ->line(__('This password reset link will expire in :count hours.', [
                'count' => $expirationDuration
            ]))
            ->action(__('Reset Password'), $url)
            ->line(__('If you did not request a password reset, it’s likely that another user has entered your email address by mistake and your account is still secure. If you believe an unauthorized person has accessed your account, you can reset your password at [:domain](:url).', ['domain' => config('app.domain'), 'url' => config('app.url')]))
            ->salutation('Kurozora Support');
    }
}
