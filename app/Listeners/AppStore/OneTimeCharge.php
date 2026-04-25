<?php

namespace App\Listeners\AppStore;

use App\Models\ConsumablePurchase;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class OneTimeCharge extends AppStoreListener
{
    protected function process($event, AppStoreV2ServerNotification $notification, V2DecodedPayload $payload): void
    {
        $transactionInfo = $payload->getTransactionInfo();

        $user = $this->resolveUser($transactionInfo->getAppAccountToken());
        if (!$user) {
            return;
        }

        $product = $this->resolveProduct($transactionInfo->getProductId());
        if (!$product) {
            return;
        }

        $this->upsertTransaction($transactionInfo, $product, $user->uuid);

        $now = now();

        ConsumablePurchase::upsert(
            [[
                'transaction_id' => $transactionInfo->getTransactionId(),
                'user_id' => $user->uuid,
                'product_id' => $product->product_id,
                'purchased_at' => $transactionInfo->getPurchaseDate()?->toDateTime(),
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['transaction_id'],
            ['user_id', 'product_id', 'purchased_at', 'updated_at'],
        );

        $this->recomputeUserEntitlements($user);
        $this->notifyUserAboutUpdate($user, $event, $product);
    }
}
