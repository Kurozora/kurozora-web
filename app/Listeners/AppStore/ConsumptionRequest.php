<?php

namespace App\Listeners\AppStore;

use App\Models\ConsumablePurchase;
use App\Models\StoreProduct;
use App\Models\UserReceiptTransaction;
use AppStoreServerLibrary\Models\AccountTenure;
use AppStoreServerLibrary\Models\ConsumptionStatus;
use AppStoreServerLibrary\Models\DeliveryStatus;
use AppStoreServerLibrary\Models\LifetimeDollarsPurchased;
use AppStoreServerLibrary\Models\LifetimeDollarsRefunded;
use AppStoreServerLibrary\Models\Platform;
use AppStoreServerLibrary\Models\PlayTime;
use AppStoreServerLibrary\Models\RefundPreference;
use AppStoreServerLibrary\Models\UserStatus;
use Exception;
use Imdhemy\AppStore\ServerNotifications\V2DecodedPayload;
use Imdhemy\Purchases\ServerNotifications\AppStoreV2ServerNotification;

class ConsumptionRequest extends AppStoreListener
{
    protected function process($event, AppStoreV2ServerNotification $notification, V2DecodedPayload $payload): void
    {
        $transactionInfo = $payload->getTransactionInfo();
        $transactionId = $transactionInfo->getTransactionId();

        $purchase = ConsumablePurchase::with('user')
            ->where('transaction_id', $transactionId)
            ->first();

        if (!$purchase || !$purchase->user) {
            return;
        }

        $user = $purchase->user;
        $days = $user->created_at->diffInDays();
        $accountTenure = match (true) {
            $days <= 3 => AccountTenure::ZERO_TO_THREE_DAYS,
            $days <= 10 => AccountTenure::THREE_DAYS_TO_TEN_DAYS,
            $days <= 30 => AccountTenure::TEN_DAYS_TO_THIRTY_DAYS,
            $days <= 90 => AccountTenure::THIRTY_DAYS_TO_NINETY_DAYS,
            $days <= 180 => AccountTenure::NINETY_DAYS_TO_ONE_HUNDRED_EIGHTY_DAYS,
            $days <= 365 => AccountTenure::ONE_HUNDRED_EIGHTY_DAYS_TO_THREE_HUNDRED_SIXTY_FIVE_DAYS,
            $days > 365 => AccountTenure::GREATER_THAN_THREE_HUNDRED_SIXTY_FIVE_DAYS,
            default => AccountTenure::UNDECLARED,
        };

        $totalSpent = ConsumablePurchase::where('user_id', $purchase->user_id)
            ->join(StoreProduct::TABLE_NAME, 'consumable_purchases.product_id', '=', StoreProduct::TABLE_NAME . '.product_id')
            ->sum(StoreProduct::TABLE_NAME . '.price_usd_milliunits') / 1000;
        $lifetimePurchased = match (true) {
            $totalSpent <= 0.0 => LifetimeDollarsPurchased::ZERO_DOLLARS,
            $totalSpent < 50 => LifetimeDollarsPurchased::ONE_CENT_TO_FORTY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalSpent < 100 => LifetimeDollarsPurchased::FIFTY_DOLLARS_TO_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalSpent < 500 => LifetimeDollarsPurchased::ONE_HUNDRED_DOLLARS_TO_FOUR_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalSpent < 1000 => LifetimeDollarsPurchased::FIVE_HUNDRED_DOLLARS_TO_NINE_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalSpent < 2000 => LifetimeDollarsPurchased::ONE_THOUSAND_DOLLARS_TO_ONE_THOUSAND_NINE_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            default => LifetimeDollarsPurchased::TWO_THOUSAND_DOLLARS_OR_GREATER,
        };

        $totalRefunded = UserReceiptTransaction::whereNotNull('revoked_at')
            ->where('user_id', $purchase->user_id)
            ->join(StoreProduct::TABLE_NAME, UserReceiptTransaction::TABLE_NAME . '.product_id', '=', StoreProduct::TABLE_NAME . '.product_id')
            ->sum(StoreProduct::TABLE_NAME . '.price_usd_milliunits') / 1000;
        $lifetimeRefunded = match (true) {
            $totalRefunded <= 0.0 => LifetimeDollarsRefunded::ZERO_DOLLARS,
            $totalRefunded < 50 => LifetimeDollarsRefunded::ONE_CENT_TO_FORTY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalRefunded < 100 => LifetimeDollarsRefunded::FIFTY_DOLLARS_TO_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalRefunded < 500 => LifetimeDollarsRefunded::ONE_HUNDRED_DOLLARS_TO_FOUR_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalRefunded < 1000 => LifetimeDollarsRefunded::FIVE_HUNDRED_DOLLARS_TO_NINE_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            $totalRefunded < 2000 => LifetimeDollarsRefunded::ONE_THOUSAND_DOLLARS_TO_ONE_THOUSAND_NINE_HUNDRED_NINETY_NINE_DOLLARS_AND_NINETY_NINE_CENTS,
            default => LifetimeDollarsRefunded::TWO_THOUSAND_DOLLARS_OR_GREATER,
        };

        $purchaseCount = ConsumablePurchase::where('user_id', $purchase->user_id)->count();
        $refundCount = UserReceiptTransaction::where('user_id', $purchase->user_id)
            ->whereNotNull('revoked_at')
            ->count();
        $refundRatio = $purchaseCount > 0 ? $refundCount / $purchaseCount : 0;
        $refundPreference = $refundRatio > 0.5
            ? RefundPreference::PREFER_DECLINE
            : RefundPreference::PREFER_GRANT;

        $consumptionRequest = new \AppStoreServerLibrary\Models\ConsumptionRequest(
            customerConsented: true,
            consumptionStatus: ConsumptionStatus::FULLY_CONSUMED,
            platform: Platform::APPLE,
            sampleContentProvided: false,
            deliveryStatus: DeliveryStatus::DELIVERED_AND_WORKING_PROPERLY,
            appAccountToken: $purchase->user_id,
            accountTenure: $accountTenure,
            playTime: PlayTime::UNDECLARED,
            lifetimeDollarsRefunded: $lifetimeRefunded,
            lifetimeDollarsPurchased: $lifetimePurchased,
            userStatus: UserStatus::ACTIVE,
            refundPreference: $refundPreference,
        );

        $client = appStore($transactionInfo->getEnvironment());

        try {
            $client->sendConsumptionData($transactionId, $consumptionRequest);
        } catch (Exception $e) {
            logger()->error('Failed to send consumption info: ' . $e->getMessage());
        }
    }
}
