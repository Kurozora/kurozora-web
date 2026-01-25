<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends VerifyEmailNotification
{
    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Verify your :x email address', ['x' => config('app.name')]))
            ->line(__('You have recently created a :x account. Please click the button below to verify this email address belongs to you.', ['x' => config('app.name')]))
            ->action(__('Verify Email Address'), $url)
            ->line(__('If you did not create an account, it’s likely that another user has entered your email address by mistake. Don’t worry, to reclaim ownership you can reset the password at [:domain/forgot-password](:url).', ['domain' => config('app.domain'), 'url' => route('password.request')]))
            ->salutation(__(':x Support', ['x' => config('app.name')]));
    }
}
