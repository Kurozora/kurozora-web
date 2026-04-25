<?php

namespace App\Listeners\AppStore;

use App\Models\UserReceipt;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class InteractiveRenewal extends AppStoreListener
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

        $this->upsertTransaction($transactionInfo, $product, $user->uuid);

        $receipt = UserReceipt::where('original_transaction_id', $transactionInfo->getOriginalTransactionId())->first();
        if (!$receipt) {
            return;
        }

        $receipt->update([
            'product_id' => $product->product_id,
            'expires_at' => $transactionInfo->getExpiresDate()?->toDateTime(),
            'is_subscribed' => true,
            'auto_renew_product_id' => $renewalInfo->getAutoRenewProductId(),
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
            'expiration_intent' => $renewalInfo->getExpirationIntent(),
            'grace_period_expires_date' => $renewalInfo->getGracePeriodExpiresDate()?->toDateTime(),
        ]);

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
