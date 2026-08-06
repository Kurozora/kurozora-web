<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class DidChangeRenewalPref extends AppStoreListener
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

        $update = [
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
        ];

        if ($autoRenewProductId = $renewalInfo->getAutoRenewProductId()) {
            if ($this->resolveProduct($autoRenewProductId)) {
                $update['auto_renew_product_id'] = $autoRenewProductId;
            }
        }

        // User cancelled downgrade — renewal product reverts to the current one.
        if (empty($notification->getSubtype())) {
            $update['auto_renew_product_id'] = $receipt->product_id;
        }

        $receipt->update($update);

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
