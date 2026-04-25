<?php

namespace App\Listeners\AppStore;

use App\Models\UserReceipt;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class PriceIncrease extends AppStoreListener
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

        $receipt = UserReceipt::where('original_transaction_id', $transactionInfo->getOriginalTransactionId())->first();
        if (!$receipt) {
            return;
        }

        $receipt->update([
            'auto_renew_product_id' => $renewalInfo->getAutoRenewProductId(),
            'will_auto_renew' => $renewalInfo->getAutoRenewStatus() === 1,
            'will_price_increase' => $renewalInfo->getPriceIncreaseStatus() === 1,
            'expiration_intent' => $renewalInfo->getExpirationIntent(),
            'grace_period_expires_date' => $renewalInfo->getGracePeriodExpiresDate()?->toDateTime(),
        ]);

        // Notify the user when consent for the price increase is still required.
        if ($renewalInfo->getPriceIncreaseStatus() === 0) {
            $this->notifyUserAboutUpdate($user, $event, $product, $receipt);
        }
    }
}
