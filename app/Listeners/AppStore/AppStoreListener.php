<?php

namespace App\Listeners\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\AppStoreNotification;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceipt;
use App\Models\UserReceiptTransaction;
use App\Notifications\SubscriptionStatus;
use App\Services\EntitlementService;
use Illuminate\Support\Facades\DB;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\AppStore\ValueObjects\JwsRenewalInfo;
use Imdhemy\AppStore\ValueObjects\JwsTransactionInfo;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

abstract class AppStoreListener implements HandlesSubscription
{
    /**
     * {@inheritDoc}
     */
    final public function handle($event): void
    {
        /** @var AppStoreV2ServerNotification $notification */
        $notification = $event->getServerNotification();
        $payload = V2DecodedPayload::fromArray($notification->getPayload());
        $notificationUUID = $payload->getNotificationUUID();

        if (AppStoreNotification::where('notification_uuid', $notificationUUID)->exists()) {
            return;
        }

        $transactionInfo = $payload->getTransactionInfo();

        AppStoreNotification::create([
            'notification_uuid' => $notificationUUID,
            'notification_type' => $notification->getType(),
            'subtype' => $notification->getSubtype(),
            'transaction_id' => $transactionInfo?->getTransactionId(),
            'original_transaction_id' => $transactionInfo?->getOriginalTransactionId(),
            'payload' => $notification->getPayload(),
            'received_at' => now(),
        ]);

        DB::transaction(fn () => $this->process($event, $notification, $payload));
    }

    /**
     * Per-listener event processing, wrapped in a database transaction.
     */
    abstract protected function process(
        $event,
        AppStoreV2ServerNotification $notification,
        V2DecodedPayload $payload
    ): void;

    /**
     * Upsert the canonical {@see UserReceiptTransaction} row for this transaction.
     */
    final protected function upsertTransaction(JwsTransactionInfo $tx, StoreProduct $product, ?string $userUuid = null): UserReceiptTransaction
    {
        $claims = $tx->getClaims();
        $now = now();

        UserReceiptTransaction::upsert(
            [[
                'transaction_id' => $tx->getTransactionId(),
                'user_id' => $userUuid,
                'original_transaction_id' => $tx->getOriginalTransactionId(),
                'product_id' => $product->product_id,
                'web_order_line_item_id' => $tx->getWebOrderLineItemId(),
                'offer_id' => $tx->getOfferIdentifier(),
                'offer_type' => $tx->getOfferType(),
                'offer_period' => $claims['offerPeriod'] ?? null,
                'offer_discount_type' => $claims['offerDiscountType'] ?? null,
                'currency' => $claims['currency'] ?? null,
                'price_milliunits' => $claims['price'] ?? null,
                'price_usd_milliunits' => $product->price_usd_milliunits,
                'quantity' => $tx->getQuantity(),
                'is_trial_period' => (bool) ($claims['isTrialPeriod'] ?? false),
                'is_in_intro_offer_period' => (bool) ($claims['isInIntroOfferPeriod'] ?? false),
                'is_upgraded' => (bool) $tx->getIsUpgraded(),
                'purchased_at' => $tx->getPurchaseDate()?->toDateTime(),
                'expires_at' => $tx->getExpiresDate()?->toDateTime(),
                'revoked_at' => $tx->getRevocationDate()?->toDateTime(),
                'revocation_reason' => $tx->getRevocationReason(),
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['transaction_id'],
            [
                'user_id', 'original_transaction_id', 'product_id', 'web_order_line_item_id',
                'offer_id', 'offer_type', 'offer_period', 'offer_discount_type',
                'currency', 'price_milliunits', 'price_usd_milliunits', 'quantity',
                'is_trial_period', 'is_in_intro_offer_period', 'is_upgraded',
                'purchased_at', 'expires_at', 'revoked_at', 'revocation_reason',
                'updated_at',
            ],
        );

        return UserReceiptTransaction::where('transaction_id', $tx->getTransactionId())->first();
    }

    /**
     * Find-or-create the subscription receipt and return it.
     */
    final protected function upsertReceipt(JwsTransactionInfo $tx, ?JwsRenewalInfo $renewalInfo = null): UserReceipt
    {
        $now = now();

        UserReceipt::upsert(
            [[
                'original_transaction_id' => $tx->getOriginalTransactionId(),
                'user_id' => $tx->getAppAccountToken(),
                'product_id' => $tx->getProductId(),
                'subscription_group_id' => $tx->getSubscriptionGroupIdentifier(),
                'original_purchased_at' => $tx->getOriginalPurchaseDate()?->toDateTime(),
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['original_transaction_id'],
            ['user_id', 'product_id', 'subscription_group_id', 'original_purchased_at', 'updated_at'],
        );

        return UserReceipt::where('original_transaction_id', $tx->getOriginalTransactionId())->first();
    }

    /**
     * Resolve the owning user from an app-account-token.
     */
    final protected function resolveUser(?string $appAccountToken): ?User
    {
        if (empty($appAccountToken)) {
            return null;
        }

        return User::where('uuid', $appAccountToken)->first();
    }

    /**
     * Resolve the product row backing this transaction.
     */
    final protected function resolveProduct(?string $productId): ?StoreProduct
    {
        if (empty($productId)) {
            return null;
        }

        return StoreProduct::where('product_id', $productId)->first();
    }

    /**
     * {@inheritDoc}
     */
    final public function isInGracePeriod(JwsRenewalInfo $renewalInfo): bool
    {
        return $renewalInfo->getIsInBillingRetryPeriod() &&
            $renewalInfo->getGracePeriodExpiresDate() !== null &&
            $renewalInfo->getGracePeriodExpiresDate()->isFuture();
    }

    /**
     * {@inheritDoc}
     */
    final public function notifyUserAboutUpdate(?User $user, $event, StoreProduct $product, ?UserReceipt $receipt = null): void
    {
        if (empty($user)) {
            return;
        }

        $notification = $event->getServerNotification();
        $user->notify(new SubscriptionStatus($notification->getType(), $notification->getSubtype(), $product, $receipt,));
    }

    /**
     * {@inheritDoc}
     */
    final public function recomputeUserEntitlements(User $user): void
    {
        EntitlementService::recompute($user);
    }
}
