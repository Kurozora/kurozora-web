<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class Expired extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\Expired $event
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

        // Collect dates
        $expiresDate = $receiptInfo->getExpiresDate();

        // Decide validity of the subscription and whether it will auto-renew
        $isSubscriptionValid = $expiresDate->isFuture();

        // Find the user and update their receipt.
        $userReceipt = $this->findUserReceipt($userID, $originalTransactionID);
        $userReceipt->is_subscribed = $isSubscriptionValid;
        $userReceipt->expired_at = $expiresDate->toDateTime();
        $userReceipt->save();

        $userReceipt->user->is_subscribed = $isSubscriptionValid;
        $userReceipt->user->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($userReceipt->user, $event);
    }
}