<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyReceiptRequest;
use App\Models\UserReceipt;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;
use Imdhemy\AppStore\Receipts\ReceiptResponse;
use Imdhemy\AppStore\ValueObjects\PendingRenewal;
use Imdhemy\Purchases\Facades\Subscription;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StoreController extends Controller
{
    /**
     * Verify the App Store receipt.
     *
     * @param VerifyReceiptRequest $request
     *
     * @return JsonResponse
     *
     * @throws GuzzleException
     * @throws InvalidReceiptException
     */
    function verifyReceipt(VerifyReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $receipt = $data['receipt'];
        logger()->channel('stack')->critical($receipt);
        $receiptResponse = $this->validated($receipt);
        $receiptStatus = $receiptResponse->getStatus();
        $needsRefresh = $receiptResponse->getReceipt()->getInApp() == null;
        $isSubscriptionValid = false;

        if (!$receiptStatus->isValid() &&
            $receiptResponse->getReceipt()->getBundleId() != config('app.ios.bundle_id')) {
            throw new ConflictHttpException('The generated receipt is invalid.');
        }

        if (!$needsRefresh) {
            // We can loop all of them or either get the first one (recently purchased).
            // Currently, we only need to verify recent purchase.
            $latestReceiptInfo = $receiptResponse->getLatestReceiptInfo();
            $receiptInfo = $latestReceiptInfo[0];

            // Collect IDs
            $userID = $receiptInfo->getAppAccountToken();
            $originalTransactionID = $receiptInfo->getOriginalTransactionId();
            $webOrderLineItemID = $receiptInfo->getWebOrderLineItemId();
            $offerID = $receiptInfo->getPromotionalOfferId();
            $subscriptionGroupID = $receiptInfo->getSubscriptionGroupIdentifier();
            $productID = $receiptInfo->getProductId();

            // Collect dates
            $originalPurchaseDate = $receiptInfo->getOriginalPurchaseDate();
            $purchaseDate = $receiptInfo->getPurchaseDate();
            $expiresDate = $receiptInfo->getExpiresDate();
            $revokedDate = $receiptInfo->getCancellationDate();

            // Check for grace period
            $pendingRenewalInfo = $receiptResponse->getPendingRenewalInfo()[0];
            $isInGracePeriod = $this->isInGracePeriod($pendingRenewalInfo);

            // Decide validity of the subscription and whether it will auto-renew
            $isSubscriptionValid = $expiresDate?->isFuture() || $isInGracePeriod;
            $willAutoRenew = $pendingRenewalInfo->getAutoRenewStatus();

            // Save user receipt
            $userReceipt = UserReceipt::firstWhere([
                ['user_id', '=', $userID],
                ['original_transaction_id', '=', $originalTransactionID]
            ]);

            if (empty($userReceipt)) {
                $userReceipt = UserReceipt::create([
                    'user_id' => $userID,
                    'original_transaction_id' => $originalTransactionID,
                    'web_order_line_item_id' => $webOrderLineItemID,
                    'offer_id' => $offerID,
                    'subscription_group_id' => $subscriptionGroupID,
                    'product_id' => $productID,
                    'is_subscribed' => $isSubscriptionValid,
                    'will_auto_renew' => $willAutoRenew,
                    'original_purchased_at' => $originalPurchaseDate?->toDateTime(),
                    'purchased_at' => $purchaseDate?->toDateTime(),
                    'expired_at' => $expiresDate?->toDateTime(),
                    'revoked_at' => $revokedDate?->toDateTime(),
                ]);
            }

            // Update user
            $updateUserAttributes = [
                'is_pro' => $isSubscriptionValid,
                'is_subscribed' => $isSubscriptionValid
            ];

            if (!empty($purchaseDate)) {
                $updateUserAttributes['subscribed_at'] = $purchaseDate->toDateTime();
            }

            $user = $userReceipt->user;
            $user?->update($updateUserAttributes);
        }

        return JSONResult::success([
            'data' => [
                'type' => 'subscription',
                'attributes' => [
                    'isValid' => $isSubscriptionValid,
                    'needsRefresh' => $needsRefresh
                ]
            ]
        ]);
    }

    /**
     * Checks with Apple if the receipt is verified.
     *
     * @param string $receiptData
     *
     * @return ReceiptResponse
     * @throws GuzzleException
     * @throws InvalidReceiptException
     */
    private function validated(string $receiptData): ReceiptResponse
    {
        // To verify auto-renewable receipt
        return Subscription::appStore()
            ->receiptData($receiptData)
            ->verifyRenewable();
    }

    /**
     * Billing retrying and grace period expires date is in the future.
     *
     * @param PendingRenewal $pendingRenewalInfo
     *
     * @return bool
     */
    public function isInGracePeriod(PendingRenewal $pendingRenewalInfo): bool
    {
        return $pendingRenewalInfo->getIsInBillingRetryPeriod() &&
            $pendingRenewalInfo->getGracePeriodExpiresDate() !== null &&
            $pendingRenewalInfo->getGracePeriodExpiresDate()?->isFuture();
    }
}
