<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class DidFailToRenew extends AppStoreListener
{
    protected function process($event, AppStoreV2ServerNotification $notification, V2DecodedPayload $payload): void
    {
        $transactionInfo = $payload->getTransactionInfo();
        $renewalInfo = $payload->getRenewalInfo();

        $user = $this->resolveUser($transactionInfo->getAppAccountToken());
        if (!$user) {
            return;
        }

        $product = $this->resolveProduct($transactionInfo->getProductId());
        if (!$product) {
            return;
        }

        $transaction = $this->upsertTransaction($transactionInfo, $product, $user->uuid);

        $receipt = $this->upsertReceipt($transactionInfo, $renewalInfo);

        $gracePeriodExpiresDate = $renewalInfo->getGracePeriodExpiresDate();
        $isInGracePeriod = $gracePeriodExpiresDate?->isFuture() ?? false;

        $receipt->update([
            'revoked_at' => $transaction->revoked_at,
            'is_subscribed' => $isInGracePeriod,
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
            'expiration_intent' => $renewalInfo->getExpirationIntent(),
            'grace_period_expires_date' => $gracePeriodExpiresDate?->toDateTime(),
        ]);

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
