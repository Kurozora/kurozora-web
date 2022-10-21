<?php

namespace App\Listeners\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Imdhemy\Purchases\Events\PurchaseEvent;

abstract class AppStoreListener implements HandlesSubscription
{
    /**
     * Handle the received purchase event.
     *
     * @param $event
     */
    public function handle($event) { }

    /** Finds the user to which the subscription belongs.
     *
     * @param ?string $userID
     * @param string $originalTransactionID
     * @return UserReceipt|null
     */
    public function findUserReceipt(?string $userID, string $originalTransactionID): ?UserReceipt
    {
        return UserReceipt::firstWhere([
                ['user_id', '=', $userID],
                ['original_transaction_id', '=', $originalTransactionID],
            ]);
    }

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User $user
     * @param PurchaseEvent $event
     */
    public function notifyUserAboutUpdate(User $user, PurchaseEvent $event)
    {
        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }
}
