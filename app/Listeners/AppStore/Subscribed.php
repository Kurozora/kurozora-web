<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class Subscribed extends AppStoreListener
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
        $receipt = $this->upsertReceipt($transactionInfo, $renewalInfo);

        $receipt->update([
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
            'grace_period_expires_date' => $renewalInfo->getGracePeriodExpiresDate()?->toDateTime(),
        ]);

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
