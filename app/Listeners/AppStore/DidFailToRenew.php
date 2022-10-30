<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\AppStore\ValueObjects\JwsRenewalInfo;

class DidFailToRenew extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\DidFailToRenew $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();
        $receiptInfo = $providerRepresentation->getTransactionInfo();

        // Collect dates
        $expiresDate = $receiptInfo->getExpiresDate();

        // Check for grace period
        $renewalInfo = $providerRepresentation->getRenewalInfo();
        $isInGracePeriod = $this->isInGracePeriod($renewalInfo);

        // Decide validity of the subscription and whether it will auto-renew
        $isSubscriptionValid = $expiresDate->isFuture() || $isInGracePeriod;

        // Find the user and update their receipt.
        $userReceipt = $this->findOrCreateUserReceipt($providerRepresentation);
        $userReceipt->update([
            'is_subscribed' => $isSubscriptionValid,
            'expired_at' => $expiresDate?->toDateTime()
        ]);

        // Update user values.
        $user = $userReceipt->user;
        $user->update([
            'is_subscribed' => $isSubscriptionValid
        ]);

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
