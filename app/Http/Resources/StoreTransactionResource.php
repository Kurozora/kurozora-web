<?php

namespace App\Http\Resources;

use App\Models\UserReceiptTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreTransactionResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var UserReceiptTransaction $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->transaction_id,
            'type' => 'store-transactions',
            'attributes' => [
                'transactionID' => (string) $this->resource->transaction_id,
                'productType' => $this->resource->storeProduct?->type?->value,
                'productID' => $this->resource->product_id,
                'priceMilliunits' => $this->resource->price_milliunits,
                'currency' => $this->resource->currency,
                'purchasedAt' => $this->resource->purchased_at?->timestamp,
                'expiresAt' => $this->resource->expires_at?->timestamp,
                'revokedAt' => $this->resource->revoked_at?->timestamp,
                'isRefundable' => $this->resource->is_refundable,
            ],
        ];
    }
}
