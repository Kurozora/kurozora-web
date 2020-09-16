<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetStoreRequest;
use App\Http\Requests\VerifyReceiptRequest;
use App\Http\Resources\StoreProductResource;
use App\StoreProduct;
use App\User;
use App\UserReceipt;
use Auth;
use Http;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
     */
    function verifyReceipt(VerifyReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $receipt = $data['receipt'];
        $receiptData = $this->isVerified('https://buy.itunes.apple.com/verifyReceipt', $receipt);

        $result = collect($receiptData['receipt']['in_app']);
        $sorted = $result->sortByDesc('purchase_date');
        $sorted = $sorted->values()->all();
        if (!isset($sorted[0]))
            dd('no subscriptions');

        // If user is authenticated then connect the purchase to the user's account.
        $userID = Auth::id();

        if($userID) {
            /** @var UserReceipt $foundReceipt */
            $foundReceipt = UserReceipt::where('user_id', '=', $userID)->first();

            if ($foundReceipt) {
                $foundReceipt->receipt = $receipt;
                $foundReceipt->save();
            } else {
                UserReceipt::create([
                    'receipt' => $receipt,
                    'user_id' => $userID
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Checks with Apple if the receipt is verified.
     *
     * @param string $verificationServer
     * @param string $receiptData
     * @return array
     */
    private function isVerified(string $verificationServer, string $receiptData): array
    {
        $requestBody = [
            'receipt-data' => $receiptData,
            'password' => config('services.apple.store_kit.password'),
            'exclude-old-transactions' => true
        ];
        $requestBodyJSON = json_encode($requestBody);

        $response = Http::withBody($requestBodyJSON, 'application/json')->post($verificationServer);
        $data = json_decode($response->body(), true);

        if ($data['status'] == 21007) {
            return $this->isVerified('https://sandbox.itunes.apple.com/verifyReceipt', $receiptData);
        }  else if ($data['status'] != 0) {
            throw new ServiceUnavailableHttpException(null, 'There was a problem validating the receipt, please try again later.');
        }

        return $data;
    }
}
