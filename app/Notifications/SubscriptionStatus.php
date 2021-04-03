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

    /** @var string $subscriptionStatus */
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
    public function via($notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
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
        $apnMessage = ApnMessage::create()
            ->title('Subscription Update')
            ->body(self::getDescription($this->subscriptionStatus))
            ->badge($notifiable->unreadNotifications()->count());

        $apnMessage->body('Your subscription renewal preference has been successfully updated.');

        return $apnMessage;
    }

    /**
     * Get the description associated with the subscription status.
     *
     * @param string $subscriptionStatus
     * @return string
     */
    static function getDescription(string $subscriptionStatus): string
    {
        switch ($subscriptionStatus) {
            case 'CANCEL':
                return 'Your subscription has been successfully canceled.';
            case 'DID_CHANGE_RENEWAL_PREF':
                return 'Your subscription renewal preference has been successfully updated.';
            case 'DID_CHANGE_RENEWAL_STATUS':
                return 'Your subscription renewal status has been successfully changed.';
            case 'DID_FAIL_TO_RENEW':
                return 'Failed to renew subscription due to a billing issue.';
            case 'DID_RECOVER':
                return 'Your expired subscription has been successfully recovered.';
            case 'DID_RENEW':
                return 'Your subscription has been auto-renewed successfully for a new transaction period.';
            case 'INITIAL_BUY':
                return 'Your transaction has been successfully completed.';
            case 'INTERACTIVE_RENEWAL':
                return 'You have successfully renewed your subscription.';
            case 'PRICE_INCREASE_CONSENT':
                return 'The subscription price increase has been accepted.';
            case 'REFUND':
                return 'AppleCare has successfully refunded your subscription transaction.';
            case 'REVOKE':
                return 'Your subscription was revoked due to the purchaser disabling Family Sharing.';
        }
        return 'Your transaction has been completed.';
    }
}
