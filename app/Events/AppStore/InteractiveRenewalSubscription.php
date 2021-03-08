<?php

namespace App\Events\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Imdhemy\AppStore\Receipts\ReceiptResponse;
use Imdhemy\Purchases\Events\AppStore\InteractiveRenewal;

class InteractiveRenewalSubscription implements HandlesSubscription
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param InteractiveRenewal $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();

        // Find the user and update their receipt.
        $user = $this->findUserBySubscriptionId($uniqueIdentifier);
        $user->receipt->is_subscribed = true;
        $user->receipt->upgrade_date = now()->toDateString();
        $user->receipt->cancellation_date = null;
        $user->save();

        // Notify the user about the subscription update.
        if(Env('APP_ENV') === 'local') {
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
     * @param InteractiveRenewal $event
     */
    public function notifyUserAboutUpdate(User $user, $event)
    {
        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }
}
