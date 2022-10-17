<?php

namespace App\Contracts\AppStore;

use App\Models\User;
use App\Models\UserReceipt;
use Imdhemy\Purchases\Events\PurchaseEvent;

interface HandlesSubscription
{
    /**
     * Handle the received purchase event.
     *
     * @param $event
     */
    public function handle($event);

    /**
     * Returns the user to which the subscription belongs.
     *
     * @param string $userID
     * @param string $originalTransactionID
     * @return UserReceipt|null
     */
    public function findUserReceipt(string $userID, string $originalTransactionID): ?UserReceipt;

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User $user
     * @param PurchaseEvent $event
     */
    public function notifyUserAboutUpdate(User $user, PurchaseEvent $event);
}
