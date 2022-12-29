<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class Revoke extends AppStoreListener
{
    /**
     * Handle the received Revoke subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\Revoke $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();
        $receiptInfo = $providerRepresentation->getTransactionInfo();

        // Collect Dates
        $expiresDate = $receiptInfo->getExpiresDate();
        $revocationDate = $receiptInfo->getRevocationDate();

        // Find the user and update their receipt.
        $userReceipt = $this->findOrCreateUserReceipt($providerRepresentation);
        $userReceipt->update([
            'is_subscribed' => false,
            'expired_at' => $expiresDate?->toDateTime(),
            'revoked_at' => $revocationDate?->toDateTime(),
        ]);

        // Update user values.
        $user = $userReceipt->user;
        $user?->update([
            'is_subscribed' => false
        ]);

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
