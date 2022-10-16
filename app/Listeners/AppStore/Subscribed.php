<?php

namespace App\Listeners\AppStore;

use App\Models\UserReceipt;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\AppStore\ValueObjects\JwsRenewalInfo;

class Subscribed extends AppStoreListener
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param \Imdhemy\Purchases\Events\AppStore\Subscribed $event
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
        $webOrderLineItemID = $receiptInfo->getWebOrderLineItemId();
        $offerID = $receiptInfo->getOfferIdentifier();
        $subscriptionGroupID = $receiptInfo->getSubscriptionGroupIdentifier();
        $productID = $receiptInfo->getProductId();

        // Collect dates
        $originalPurchaseDate = $receiptInfo->getOriginalPurchaseDate();
        $purchaseDate = $receiptInfo->getPurchaseDate();
        $expiresDate = $receiptInfo->getExpiresDate();
        $revocationDate = $receiptInfo->getRevocationDate();

        // Check for grace period
        $renewalInfo = $providerRepresentation->getRenewalInfo();
        $isInGracePeriod = $this->isInGracePeriod($renewalInfo);

        // Decide validity of the subscription and whether it will auto-renew
        $isSubscriptionValid = $expiresDate->isFuture() || $isInGracePeriod;
        $willAutoRenew = $renewalInfo->getAutoRenewStatus();

        // Find the user and update their receipt.
        $userReceipt = $this->findUserReceipt($userID, $originalTransactionID);

        if (empty($userReceipt)) {
            // Save user receipt
            $userReceipt = UserReceipt::create([
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
                'expired_at'                => $expiresDate->toDateTime(),
                'revoked_at'                => $revocationDate?->toDateTime()
            ]);
        } else {
            $userReceipt->web_order_line_item_id = $webOrderLineItemID;
            $userReceipt->offer_id = $offerID;
            $userReceipt->subscription_group_id = $subscriptionGroupID;
            $userReceipt->product_id = $productID;
            $userReceipt->is_subscribed = $isSubscriptionValid;
            $userReceipt->will_auto_renew = $willAutoRenew;
            $userReceipt->original_purchased_at = $originalPurchaseDate?->toDateTime();
            $userReceipt->purchased_at = $purchaseDate?->toDateTime();
            $userReceipt->expired_at = $expiresDate->toDateTime();
            $userReceipt->revoked_at = $revocationDate?->toDateTime();
        }

        // Update values.
        $userReceipt->save();
        $userReceipt->user->is_subscribed = $isSubscriptionValid;
        $userReceipt->user->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($userReceipt->user, $event);
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
