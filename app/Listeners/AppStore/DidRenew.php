<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class DidRenew extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\DidRenew $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();
        $receiptInfo = $providerRepresentation->getTransactionInfo();

        // Collect IDs
        $userID = $receiptInfo->getAppAccountToken();
        $originalTransactionID = $receiptInfo->getOriginalTransactionId();

        // Collect Dates
        $expirationDate = $subscription->getExpiryTime();

        // Find the user and update their receipt.
        $userReceipt = $this->findUserReceipt($userID, $originalTransactionID);
        $userReceipt->update([
            'is_subscribed' => true,
            'expired_at' => $expirationDate->toDateTime(),
            'revoked_at' => null
        ]);

        // Update user values.
        $user = $userReceipt->user;
        $user->update([
            'is_subscribed' => true
        ]);

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
