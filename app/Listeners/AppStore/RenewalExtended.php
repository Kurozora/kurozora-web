<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class RenewalExtended extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\RenewalExtended $event
     */
    public function handle($event): void
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();

        // Collect Dates
        $expirationTime = $subscription->getExpiryTime();

        // Find the user and update their receipt.
        $userReceipt = $this->findOrCreateUserReceipt($providerRepresentation);
        $userReceipt->update([
            'is_subscribed' => true,
            'expired_at' => $expirationTime->toDateTime(),
            'revoked_at' => null
        ]);

        // Update user values.
        $user = $userReceipt->user;
        $user?->update([
            'is_subscribed' => true
        ]);

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
