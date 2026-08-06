<?php

namespace App\Listeners\AppStore;

use App\Enums\StoreProductType;
use App\Models\ConsumablePurchase;
use App\Models\UserReceipt;
use App\Models\UserReceiptTransaction;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class Refund extends AppStoreListener
{
    protected function process($event, AppStoreV2ServerNotification $notification, V2DecodedPayload $payload): void
    {
        $transactionInfo = $payload->getTransactionInfo();

        $product = $this->resolveProduct($transactionInfo->getProductId());
        if (!$product) {
            return;
        }

        $revokedAt = $transactionInfo->getRevocationDate()?->toDateTime() ?? now();

        UserReceiptTransaction::where('transaction_id', $transactionInfo->getTransactionId())
            ->update([
                'revoked_at' => $revokedAt,
                'revocation_reason' => $transactionInfo->getRevocationReason(),
            ]);

        $owner = null;
        $receipt = null;

        if ($product->type->is(StoreProductType::Consumable)) {
            $purchase = ConsumablePurchase::with('user')
                ->where('transaction_id', $transactionInfo->getTransactionId())
                ->first();

            $purchase?->update(['revoked_at' => $revokedAt]);
            $owner = $purchase?->user;
        }

        if ($product->type->is(StoreProductType::AutoRenewingSubscription)) {
            $receipt = UserReceipt::with('user')
                ->where('original_transaction_id', $transactionInfo->getOriginalTransactionId())
                ->first();

            $receipt?->update([
                'expires_at' => $transactionInfo->getExpiresDate()?->toDateTime() ?? $revokedAt,
                'revoked_at' => $revokedAt,
            ]);
            $owner = $receipt?->user;
        }

        if ($owner) {
            $this->recomputeUserEntitlements($owner);
            $this->notifyUserAboutUpdate($owner, $event, $product, $receipt);
        }
    }
}
