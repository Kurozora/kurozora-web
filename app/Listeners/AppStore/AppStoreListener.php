<?php

namespace App\Listeners\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Exception;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\AppStore\ValueObjects\JwsRenewalInfo;
use Imdhemy\Purchases\Events\PurchaseEvent;

abstract class AppStoreListener implements HandlesSubscription
{
    /**
     * Handle the received purchase event.
     *
     * @param $event
     */
    public function handle($event) { }

    /**
     * Finds the user to which the subscription belongs.
     *
     * @param ?string $userID
     * @param string $originalTransactionID
     * @return UserReceipt|null
     */
    public function findUserReceipt(?string $userID, string $originalTransactionID): ?UserReceipt
    {
        $receipt = UserReceipt::firstWhere('original_transaction_id', '=', $originalTransactionID);

        // Return receipt if no user ID is provided or no receipt is found
        if (empty($userID) || empty($receipt)) {
            return $receipt;
        }

        // Update the user ID if it's not already set
        // This is a corrective measure for missing user ID in some receipts
        if (empty($receipt->user_id)) {
            $receipt->user_id = $userID;
            $receipt->save();
        }

        // If the receipt belongs to another user, return null
        if ($receipt->user_id != $userID) {
            return null;
        }

        // Return the receipt
        return $receipt;
    }

    /**
     * Creates and returns a new user receipt.
     *
     * @param V2DecodedPayload $providerRepresentation
     * @return UserReceipt
     */
    public function findOrCreateUserReceipt(V2DecodedPayload $providerRepresentation): UserReceipt
    {
        logger()->channel('stack')->critical(print_r(request()->all(), true));
        logger()->channel('stack')->critical(print_r($providerRepresentation->toArray(), true));
        $receiptInfo = $providerRepresentation->getTransactionInfo();
        $userID = $receiptInfo->getAppAccountToken();
        $originalTransactionID = $receiptInfo->getOriginalTransactionId();

        if ($userReceipt = $this->findUserReceipt($userID, $originalTransactionID)) {
            return $userReceipt;
        }

        // Collect IDs
        $webOrderLineItemID = $receiptInfo->getWebOrderLineItemId();
        $offerID = $receiptInfo->getOfferIdentifier();
        $subscriptionGroupID = $receiptInfo->getSubscriptionGroupIdentifier();
        $productID = $receiptInfo->getProductId();

        // Collect dates
        $originalPurchaseDate = $receiptInfo->getOriginalPurchaseDate();
        $purchaseDate = $receiptInfo->getPurchaseDate();
        $expiresDate = $receiptInfo->getExpiresDate();
        $revocationDate = $receiptInfo->getRevocationDate();

        try {
            // Check for grace period
            $renewalInfo = $providerRepresentation->getRenewalInfo();
            $isInGracePeriod = $this->isInGracePeriod($renewalInfo);

            // Decide validity of the subscription and whether it will auto-renew
            $isSubscriptionValid = $expiresDate?->isFuture() || $isInGracePeriod;
            $willAutoRenew = $renewalInfo->getAutoRenewStatus();
        } catch (Exception $e) {
            $willAutoRenew = false;
            $isSubscriptionValid = false;
        }

        return UserReceipt::create([
                'user_id'                   => $userID,
                'original_transaction_id'   => $originalTransactionID,
                'web_order_line_item_id'    => $webOrderLineItemID,
                'offer_id'                  => $offerID,
                'subscription_group_id'     => $subscriptionGroupID,
                'product_id'                => $productID,
                'is_subscribed'             => $isSubscriptionValid,
                'will_auto_renew'           => $willAutoRenew,
                'original_purchased_at'     => $originalPurchaseDate?->toDateTime(),
                'purchased_at'              => $purchaseDate?->toDateTime(),
                'expired_at'                => $expiresDate?->toDateTime(),
                'revoked_at'                => $revocationDate?->toDateTime()
            ]);
    }

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User|null $user
     * @param PurchaseEvent $event
     */
    public function notifyUserAboutUpdate(?User $user, PurchaseEvent $event): void
    {
        if (empty($user)) {
            return;
        }

        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }

    /**
     * Whether bill is in retrying period and grace period expiry date is in the future.
     *
     * @param JwsRenewalInfo $renewalInfo
     *
     * @return bool
     */
    public function isInGracePeriod(JwsRenewalInfo $renewalInfo): bool
    {
        return $renewalInfo->getIsInBillingRetryPeriod() &&
            $renewalInfo->getGracePeriodExpiresDate() !== null &&
            $renewalInfo->getGracePeriodExpiresDate()->isFuture();
    }
}
