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
    public function handle($event): void
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();

        // Collect IDs
        $productID = $subscription->getItemId();

        // Find the user and update their receipt.
        $userReceipt = $this->findOrCreateUserReceipt($providerRepresentation);
        $userReceipt->update([
            'product_id' => $productID
        ]);

        // Update user values.
        $user = $userReceipt->user;

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
