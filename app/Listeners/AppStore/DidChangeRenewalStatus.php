<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class DidChangeRenewalStatus extends AppStoreListener
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

        $receipt = $this->upsertReceipt($transactionInfo, $renewalInfo);

        $update = [
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
        ];

        if ($autoRenewProductId = $renewalInfo->getAutoRenewProductId()) {
            if ($this->resolveProduct($autoRenewProductId)) {
                $update['auto_renew_product_id'] = $autoRenewProductId;
            }
        }

        if ($gracePeriodExpiresDate = $renewalInfo->getGracePeriodExpiresDate()) {
            $update['grace_period_expires_date'] = $gracePeriodExpiresDate->toDateTime();
        }

        if ($expirationIntent = $renewalInfo->getExpirationIntent()) {
            $update['expiration_intent'] = $expirationIntent;
        }

        $receipt->update($update);

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
