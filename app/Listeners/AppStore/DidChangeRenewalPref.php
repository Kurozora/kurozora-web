<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class DidChangeRenewalPref extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\DidChangeRenewalPref $event
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
        $productID = $subscription->getItemId();

        // Find the user and update their receipt.
        $userReceipt = $this->findUserReceipt($userID, $originalTransactionID);
        $userReceipt->product_id = $productID;
        $userReceipt->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($userReceipt->user, $event);
    }
}