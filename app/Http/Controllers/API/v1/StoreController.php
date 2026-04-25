<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\StoreProductType;
use App\Exceptions\AppStore\AppleRootCertificateUnavailableException;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\RestoreOrderRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Models\ConsumablePurchase;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceipt;
use App\Models\UserReceiptTransaction;
use App\Services\EntitlementService;
use AppStoreServerLibrary\AppStoreServerAPIClient\APIException;
use AppStoreServerLibrary\SignedDataVerifier;
use AppStoreServerLibrary\SignedDataVerifier\VerificationException;
use AppStoreServerLibrary\SignedDataVerifier\VerificationStatus;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;
use Imdhemy\AppStore\Receipts\ReceiptResponse;
use Subscription;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class StoreController extends Controller
{
    /**
     * Verify a StoreKit receipt or signed transactions and sync the user's entitlements.
     *
     * @throws APIException
     * @throws GuzzleException
     * @throws InvalidReceiptException
     * @throws Throwable
     */
    public function verifyReceipt(VerifyReceiptRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        $needsRefresh = false;
        $environment = isset($data['environment']) ? ucfirst(strtolower((string) $data['environment'])) : null;

        try {
            DB::transaction(function () use ($data, $user, $environment, &$needsRefresh): void {
                if (!empty($data['transactions'])) {
                    $this->ingestSignedTransactions($data['transactions'], appStoreVerifier($environment), $user);
                    return;
                }

                $needsRefresh = $this->ingestLegacyReceipt($data['receipt'], $user);
            });
        } catch (AppleRootCertificateUnavailableException $e) {
            $this->handleCertificateOutage($e);
        } catch (VerificationException $e) {
            $this->mapVerificationException($e);
        }

        $entitlements = EntitlementService::recompute($user);

        return JSONResult::success([
            'data' => [
                'type' => 'subscription',
                'attributes' => [
                    'isValid' => $entitlements['pro'] || $entitlements['plus'],
                    'needsRefresh' => $needsRefresh,
                ],
            ],
        ]);
    }

    /**
     * Restore an order and ingest its signed transactions into the user's entitlements.
     *
     * @param RestoreOrderRequest $request
     *
     * @return JsonResponse
     * @throws APIException
     * @throws Throwable
     */
    public function restoreOrder(RestoreOrderRequest $request): JsonResponse
    {
        $user = auth()->user();
        $orderID = $request->validated()['orderID'];

        $response = appStore()->lookUpOrderId($orderID);
        $signedTransactions = $response->getSignedTransactions() ?? [];

        if ($signedTransactions) {
            try {
                DB::transaction(fn () => $this->ingestSignedTransactions($signedTransactions, appStoreVerifier(), $user));
            } catch (AppleRootCertificateUnavailableException $e) {
                $this->handleCertificateOutage($e);
            } catch (VerificationException $e) {
                $this->mapVerificationException($e);
            }
        }

        $entitlements = EntitlementService::recompute($user);

        return JSONResult::success([
            'data' => [
                'type' => 'subscription',
                'attributes' => [
                    'isValid' => $entitlements['pro'] || $entitlements['plus'],
                ],
            ],
        ]);
    }

    /**
     * Verify each signed JWS and persist canonical transaction/receipt rows.
     *
     * @param  string[]  $signedTransactions
     *
     * @throws VerificationException
     */
    private function ingestSignedTransactions(array $signedTransactions, SignedDataVerifier $verifier, User $user): void
    {
        foreach ($signedTransactions as $jws) {
            $payload = $verifier->verifyAndDecodeSignedTransaction($jws);

            $product = StoreProduct::where('product_id', $payload->getProductId())->first();
            if (!$product) {
                continue;
            }

            $ownerUuid = $payload->getAppAccountToken() ?? $user->uuid;

            $now = now();

            UserReceiptTransaction::upsert(
                [[
                    'transaction_id' => $payload->getTransactionId(),
                    'user_id' => $ownerUuid,
                    'original_transaction_id' => $payload->getOriginalTransactionId(),
                    'product_id' => $product->product_id,
                    'web_order_line_item_id' => $payload->getWebOrderLineItemId(),
                    'offer_id' => $payload->getOfferIdentifier(),
                    'offer_type' => $payload->getOfferType()?->value,
                    'offer_period' => $payload->getOfferPeriod(),
                    'offer_discount_type' => $payload->getOfferDiscountType()?->value,
                    'currency' => $payload->getCurrency(),
                    'price_milliunits' => $payload->getPrice(),
                    'price_usd_milliunits' => $product->price_usd_milliunits,
                    'quantity' => $payload->getQuantity(),
                    'is_upgraded' => (bool) $payload->getIsUpgraded(),
                    'purchased_at' => self::millisToCarbon($payload->getPurchaseDate()),
                    'expires_at' => self::millisToCarbon($payload->getExpiresDate()),
                    'revoked_at' => self::millisToCarbon($payload->getRevocationDate()),
                    'revocation_reason' => $payload->getRevocationReason()?->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]],
                ['transaction_id'],
                [
                    'user_id', 'original_transaction_id', 'product_id', 'web_order_line_item_id',
                    'offer_id', 'offer_type', 'offer_period', 'offer_discount_type',
                    'currency', 'price_milliunits', 'price_usd_milliunits', 'quantity',
                    'is_upgraded', 'purchased_at', 'expires_at', 'revoked_at', 'revocation_reason',
                    'updated_at',
                ],
            );

            if ($product->type->is(StoreProductType::Consumable)) {
                ConsumablePurchase::upsert(
                    [[
                        'transaction_id' => $payload->getTransactionId(),
                        'user_id' => $ownerUuid,
                        'product_id' => $product->product_id,
                        'purchased_at' => self::millisToCarbon($payload->getPurchaseDate()),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]],
                    ['transaction_id'],
                    ['user_id', 'product_id', 'purchased_at', 'updated_at'],
                );
            } else {
                UserReceipt::upsert(
                    [[
                        'original_transaction_id' => $payload->getOriginalTransactionId(),
                        'user_id' => $ownerUuid,
                        'product_id' => $product->product_id,
                        'subscription_group_id' => $payload->getSubscriptionGroupIdentifier(),
                        'original_purchased_at' => self::millisToCarbon($payload->getOriginalPurchaseDate()),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]],
                    ['original_transaction_id'],
                    ['user_id', 'product_id', 'subscription_group_id', 'original_purchased_at', 'updated_at'],
                );
            }
        }
    }

    /**
     * Verify a legacy StoreKit 1 receipt and ingest its transactions through the signed JWS pipeline.
     *
     * @throws GuzzleException
     * @throws InvalidReceiptException
     * @throws VerificationException
     */
    private function ingestLegacyReceipt(string $receiptData, User $user): bool
    {
        $response = $this->verifyLegacyReceipt($receiptData);
        $receipt = $response->getReceipt();

        if (!$response->getStatus()->isValid() || $receipt->getBundleId() !== config('app.ios.bundle_id')) {
            throw new ConflictHttpException('Invalid receipt.');
        }

        $receiptInfos = $response->getLatestReceiptInfo() ?? $receipt->getInApp();

        if (!$receiptInfos) {
            return true;
        }

        $environment = $response->getEnvironment();
        $client = appStore($environment);
        $signedTransactions = [];

        foreach ($receiptInfos as $receiptInfo) {
            try {
                $txResponse = $client->getTransactionInfo($receiptInfo->getTransactionId());
            } catch (APIException $e) {
                logger()->warning('App Store getTransactionInfo failed', [
                    'transaction_id' => $receiptInfo->getTransactionId(),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if ($signed = $txResponse->getSignedTransactionInfo()) {
                $signedTransactions[] = $signed;
            }
        }

        if ($signedTransactions) {
            $this->ingestSignedTransactions($signedTransactions, appStoreVerifier($environment), $user);
        }

        return false;
    }

    /**
     * Call Apple's `/verifyReceipt` endpoint for legacy StoreKit 1 receipt verification.
     *
     * @throws GuzzleException
     * @throws InvalidReceiptException
     */
    private function verifyLegacyReceipt(string $receiptData): ReceiptResponse
    {
        return Subscription::appStore()
            ->receiptData($receiptData)
            ->verifyRenewable();
    }

    /**
     * Handle root-certificate outage.
     *
     * @param AppleRootCertificateUnavailableException $e
     *
     * @return void
     */
    private function handleCertificateOutage(AppleRootCertificateUnavailableException $e): void
    {
        logger()->error('Apple root certificate unavailable during receipt verification.', [
            'exception' => $e->getMessage(),
        ]);

        throw new ServiceUnavailableHttpException(null, 'Apple verification service unavailable.');
    }

    /**
     * Translate an Apple JWS verification failure into the API's canonical error shape.
     *
     * @param VerificationException $e
     *
     * @return void
     */
    private function mapVerificationException(VerificationException $e): void
    {
        if (!$e->isPermanentFailure()) {
            throw new ServiceUnavailableHttpException(null, 'Apple verification service unavailable.');
        }

        if ($e->getStatus() === VerificationStatus::INVALID_ENVIRONMENT) {
            throw new ConflictHttpException('Signed transaction environment mismatch.');
        }

        throw ValidationException::withMessages([
            'transactions' => 'Signed transaction could not be verified (' . $e->getStatus()->name . ').',
        ]);
    }

    /**
     * Convert milliseconds-since-epoch into a Carbon instance.
     *
     * @param null|int $millis
     *
     * @return null|Carbon
     */
    private static function millisToCarbon(?int $millis): ?Carbon
    {
        return $millis !== null ? Carbon::createFromTimestampMs($millis) : null;
    }
}
