<?php

namespace App\Events\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Imdhemy\AppStore\Receipts\ReceiptResponse;

use Imdhemy\Purchases\Events\AppStore\DidChangeRenewalStatus;

class DidChangeRenewalStatusSubscription implements HandlesSubscription
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param DidChangeRenewalStatus $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $willAutoRenew = $event->isAutoRenewal();
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();

        // Find the user and update their receipt.
        $user = $this->findUserBySubscriptionId($uniqueIdentifier);
        $user->receipt->will_auto_renew = (int) $willAutoRenew;
        $user->save();

        // Notify the user about the subscription update.
        if ($willAutoRenew) {
            $this->notifyUserAboutUpdate($user, $event);
        }
    }

    /** Finds the user to which the subscription belongs.
     *
     * @param string $uniqueIdentifier
     * @return User
     */
    public function findUserBySubscriptionId(string $uniqueIdentifier): User
    {
        return UserReceipt::whereOriginalTransactionId($uniqueIdentifier)->first()->user;
    }

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User $user
     * @param DidChangeRenewalStatus $event
     */
    public function notifyUserAboutUpdate(User $user, $event)
    {
        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }
}
