<?php

namespace App\Listeners\AppStore;

use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class OfferRedeemed extends AppStoreListener
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

        $update = [];
        $subtype = $notification->getSubtype();

        // Upgrade takes effect immediately.
        if ($subtype === 'UPGRADE') {
            $update['product_id'] = $product->product_id;
            $update['expires_at'] = $transaction->expires_at;
        }

        // Downgrade takes effect on next renewal.
        if ($subtype === 'DOWNGRADE') {
            $update['auto_renew_product_id'] = $renewalInfo->getAutoRenewProductId();
        }

        if ($update) {
            $receipt->update($update);
        }

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
    }
}
