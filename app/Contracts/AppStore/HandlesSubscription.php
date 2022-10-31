<?php

namespace App\Contracts\AppStore;

use App\Models\User;
use App\Models\UserReceipt;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
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
     * Returns the user receipt belonging to the transaction info.
     *
     * @param ?string $userID
     * @param string $originalTransactionID
     * @return UserReceipt|null
     */
    public function findUserReceipt(?string $userID, string $originalTransactionID): ?UserReceipt;

    /**
     * Finds or creates, and returns a new user receipt.
     *
     * @param V2DecodedPayload $providerRepresentation
     * @return UserReceipt
     */
    public function findOrCreateUserReceipt(V2DecodedPayload $providerRepresentation): UserReceipt;

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User|null $user
     * @param PurchaseEvent $event
     */
    public function notifyUserAboutUpdate(?User $user, PurchaseEvent $event);
}
