<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;

class PriceIncrease extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\PriceIncrease $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();

        /** @var V2DecodedPayload $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();

        // Decide whether it will auto-renew
        $renewalInfo = $providerRepresentation->getRenewalInfo();

        // Decide whether it will auto-renew
        $willAutoRenew = $renewalInfo->getPriceIncreaseStatus() ?? 0;

        // Find the user and update their receipt.
        $userReceipt = $this->findOrCreateUserReceipt($providerRepresentation);
        $userReceipt->update([
            'will_auto_renew' => $willAutoRenew
        ]);

        // Update user values.
        $user = $userReceipt->user;

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }
}
