<?php

namespace App\Events\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Imdhemy\AppStore\Receipts\ReceiptResponse;
use Imdhemy\Purchases\Events\AppStore\InitialBuy;

class InitialBuySubscription implements HandlesSubscription
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param InitialBuy $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        /** @var ReceiptResponse $providerRepresentation */
        $providerRepresentation = $subscription->getProviderRepresentation();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $latestReceiptInfo = $providerRepresentation->getLatestReceiptInfo()[0];
        $pendingRenewalInfo = $providerRepresentation->getPendingRenewalInfo()[0];

        $originalTransactionId = $latestReceiptInfo->getOriginalTransactionId();
        $ebOrderLineItemId = $latestReceiptInfo->getWebOrderLineItemId();
        $latestReceiptData = $providerRepresentation->getLatestReceipt();
        $expirationTime = $subscription->getExpiryTime();
        $subscriptionIsValid = $expirationTime->isFuture();
        $willAutoRenew = $pendingRenewalInfo->isAutoRenewStatus();
        $subscriptionProductId = $latestReceiptInfo->getProductId();

        // Find the user and update their receipt.
        $user = $this->findUserBySubscriptionId($uniqueIdentifier);
        $user->receipt->original_transaction_id = $originalTransactionId;
        $user->receipt->web_order_line_item_id = $ebOrderLineItemId;
        $user->receipt->latest_receipt_data = $latestReceiptData;
        $user->receipt->latest_expires_date = $expirationTime;
        $user->receipt->is_subscribed = $subscriptionIsValid;
        $user->receipt->will_auto_renew = $willAutoRenew;
        $user->receipt->subscription_product_id = $subscriptionProductId;
        $user->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
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
     * @param InitialBuy $event
     */
    public function notifyUserAboutUpdate(User $user, $event)
    {
        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }
}
