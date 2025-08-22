<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class InviteToFamilyEmail extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $invitationUrl = $this->invitationUrl($notifiable);

        return $this->buildMailMessage($notifiable, $invitationUrl);
    }

    /**
     * Get the invitation email notification mail message for the given URL.
     *
     * @param object $notifiable
     * @param string $url
     *
     * @return MailMessage
     */
    protected function buildMailMessage(object $notifiable, string $url): MailMessage
    {
        return (new MailMessage)
            ->subject(__('You’re Invited to Join :x Family Sharing.', ['x' => config('app.name')]))
            ->line(__(':x has invited you to Family Sharing, so you can enjoy premium app icons, dynamic themes, integration with calendar, startup chimes, GIF for profile and more.', ['x' => auth()->user()->username]))
            ->action(__('Join Family'), $url)
            ->salutation('Kurozora Support');
    }

    /**
     * Get the invitation URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function invitationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'family.invite',
            Carbon::now()->addMinutes(Config::get('auth.family.expire', 60)),
            [
                'id' => auth()->user()->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
