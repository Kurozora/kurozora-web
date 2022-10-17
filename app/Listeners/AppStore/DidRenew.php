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
        $userReceipt->is_subscribed = true;
        $userReceipt->expired_at = $expirationDate->toDateTime();
        $userReceipt->revoked_at = null;
        $userReceipt->save();

        $userReceipt->user->is_subscribed = true;
        $userReceipt->user->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($userReceipt->user, $event);
    }
}
