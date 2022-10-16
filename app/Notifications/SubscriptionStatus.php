<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class SubscriptionStatus extends Notification
{
    use Queueable;

    /**
     * The status of the user's subscription.
     *
     * @var string $subscriptionStatus
     */
    private string $subscriptionStatus;

    /**
     * Create a new notification instance.
     *
     * @param string $subscriptionStatus
     */
    public function __construct(string $subscriptionStatus)
    {
        $this->subscriptionStatus = $subscriptionStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'subscriptionStatus' => $this->subscriptionStatus
        ];
    }

    /**
     * Get the APN representation of the notification.
     *
     * @param User $notifiable
     * @return ApnMessage
     */
    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title('Subscription Update')
            ->body(self::getDescription($this->subscriptionStatus))
            ->badge($notifiable->unreadNotifications()->count());
    }

    /**
     * Get the description associated with the subscription status.
     *
     * @param string $subscriptionStatus
     * @return string
     */
    static function getDescription(string $subscriptionStatus): string
    {
        return match ($subscriptionStatus) {
            'DID_CHANGE_RENEWAL_PREF' => 'Your subscription renewal preference has been successfully updated.',
            'DID_CHANGE_RENEWAL_STATUS' => 'Your subscription renewal status has been successfully changed.',
            'DID_FAIL_TO_RENEW' => 'Failed to renew subscription due to a billing issue.',
            'DID_RENEW' => 'Your subscription has been auto-renewed successfully for a new transaction period.',
            'EXPIRED' => 'Your subscription has expired.',
            'GRACE_PERIOD_EXPIRED' => 'Your billing grace period has ended without renewing the subscription due to a billing issue.',
            'OFFER_REDEEMED' => 'You have successfully redeemed a promotional offer.',
            'PRICE_INCREASE' => 'The subscription price increase has been accepted.',
            'REFUND' => 'Your subscription transaction was successfully refunded.',
            'RENEWAL_EXTENDED' => 'Your subscription renewal date has successfully extended.',
            'REVOKE' => 'Your subscription was revoked due to the purchaser disabling Family Sharing.',
            'SUBSCRIBED' => 'Your subscription transaction was successful. You can now enjoy Kurozora+.',
            default => 'Your transaction has been completed.',
        };
    }
}
