<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetStoreRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Http\Resources\StoreProductResource;
use App\Models\StoreProduct;
use App\Models\UserReceipt;
use Auth;
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
     * Returns the list of products in the store.
     *
     * @param GetStoreRequest $request
     *
     * @return JsonResponse
     */
    function index(GetStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $storeProducts = StoreProduct::all();

        if (isset($data['type'])) {
            $storeProducts = $storeProducts->where('type', '=', $data['type']);
        }

        return JSONResult::success([
            'data' => StoreProductResource::collection($storeProducts)
        ]);
    }

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
        $receiptData = $this->validated($receipt);

        if (!$receiptData->getStatus()->isValid() &&
            $receiptData->getReceipt()->getBundleId() != Env('APP_BUNDLE_ID')) {
            throw new ConflictHttpException('The generated receipt is invalid.');
        }

        $latestReceiptInfo = $receiptData->getLatestReceiptInfo()[0];
        $expiresDate = $latestReceiptInfo->getExpiresDate();
        $originalTransactionID = $latestReceiptInfo->getOriginalTransactionId();
        $webOrderLineItemID = $latestReceiptInfo->getWebOrderLineItemId();
        $subscriptionProductId = $latestReceiptInfo->getProductId();

        // Check for grace period.
        $pendingRenewalInfo = $receiptData->getPendingRenewalInfo()[0];
        $isInGracePeriod = $this->isInGracePeriod($pendingRenewalInfo);

        // Decide validity of the subscription and whether it will auto renew.
        $subscriptionIsValid = $expiresDate->isFuture() || $isInGracePeriod;
        $willAutoRenew = $pendingRenewalInfo->isAutoRenewStatus();

        // Get authenticated user.
        $userID = Auth::id();

        /** @var UserReceipt $foundReceipt */
        $foundReceipt = UserReceipt::whereUserId($userID)->first();

        if ($foundReceipt) {
            $foundReceipt->save([
                'latest_receipt_data' => $receipt,
                'latest_expires_date' => $expiresDate->toDateTime(),
                'is_subscribed' => $subscriptionIsValid,
                'subscription_product_id' => $subscriptionProductId
            ]);
        } else {
            UserReceipt::create([
                'user_id'                   => $userID,
                'original_transaction_id'   => $originalTransactionID,
                'web_order_line_item_id'    => $webOrderLineItemID,
                'latest_receipt_data'       => $receipt,
                'latest_expires_date'       => $expiresDate->toDateTime(),
                'is_subscribed'             => $subscriptionIsValid,
                'will_auto_renew'           => $willAutoRenew,
                'subscription_product_id'   => $subscriptionProductId
            ]);
        }

        return JSONResult::success([
            'data' => [
                'type' => 'subscription',
                'attributes' => [
                    'isValid' => $subscriptionIsValid
                ]
            ]
        ]);
    }

    /**
     * Checks with Apple if the receipt is verified.
     *
     * @param string $receiptData
     *
     *
     * @return ReceiptResponse
     *
     * @throws GuzzleException
     * @throws InvalidReceiptException
     */
    private function validated(string $receiptData): ReceiptResponse
    {
        // To verify auto-renewable receipt
        return Subscription::appStore()->receiptData($receiptData)->renewable()->verifyReceipt();
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
        return $pendingRenewalInfo->isInBillingRetryPeriod() &&
            $pendingRenewalInfo->getGracePeriodExpiresDate() !== null &&
            $pendingRenewalInfo->getGracePeriodExpiresDate()->isFuture();
    }
}
