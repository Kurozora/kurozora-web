<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetStoreRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Http\Resources\StoreProductResource;
use App\StoreProduct;
use App\UserReceipt;
use Auth;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use ReceiptValidator\iTunes\PurchaseItem;
use ReceiptValidator\iTunes\ResponseInterface as iTunesResponseInterface;
use \ReceiptValidator\iTunes\Validator as iTunesValidator;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StoreController extends Controller
{
    /**
     * Returns the list of products in the store.
     *
     * @param GetStoreRequest $request
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
     * @return JsonResponse
     * @throws GuzzleException
     */
    function verifyReceipt(VerifyReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $receipt = $data['receipt'];
        $receiptData = $this->validated($receipt);

        if ($receiptData->isValid()) {
            $result = collect($receiptData->getPurchases());

            /** @var PurchaseItem $sorted */
            $sorted = $result->whereNotNull('expires_date_ms')
                            ->whereNull(['cancellation_date'])
                            ->sortByDesc('purchase_date')->first();
            $expirationDate = $sorted->getExpiresDate();

            // Check for grace period.
            $isInGracePeriod = false;
            if(array_key_exists('grace_period_expires_date', $sorted->getRawResponse())) {
                $gracePeriod = Carbon::createFromTimestampUTC(
                    (int) round((int) $sorted->getRawResponse()['grace_period_expires_date'] / 1000)
                );

                $isInGracePeriod = $gracePeriod->isFuture();
            }
        } else {
            throw new ConflictHttpException('The generated receipt is invalid.');
        }

        // Decide validity of the subscription.
        $subscriptionIsValid = $expirationDate->isFuture() || $isInGracePeriod;
        $userID = Auth::id();

        /** @var UserReceipt $foundReceipt */
        $foundReceipt = UserReceipt::where('user_id', '=', $userID)->first();

        if ($foundReceipt) {
            $foundReceipt->receipt = $receipt;
            $foundReceipt->is_valid = $subscriptionIsValid;
            $foundReceipt->save();
        } else {
            UserReceipt::create([
                'receipt'   => $receipt,
                'user_id'   => $userID,
                'is_valid'  => $subscriptionIsValid
            ]);
        }

        return JSONResult::success([
            'data' => [
                'type' => 'receipt',
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
     * @return iTunesResponseInterface
     * @throws GuzzleException
     */
    private function validated(string $receiptData): iTunesResponseInterface
    {
        $validator = new iTunesValidator();

        try {
            $sharedSecret = config('services.apple.store_kit.password');
            $response = $validator->setExcludeOldTransactions(false)->setSharedSecret($sharedSecret)->setReceiptData($receiptData)->validate();
        } catch (Exception $e) {
            dd('got error = ' . $e->getMessage());
        }

        return $response;
    }
}
