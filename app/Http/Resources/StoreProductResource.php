<?php

namespace App\Http\Resources;

use App\Enums\StoreProductType;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /** @var StoreProduct $storeProduct */
        $storeProduct = $this->resource;

        return [
            'id'         => $storeProduct->id,
            'type'       => 'store-products',
            'href'       => route('api.store.details', $storeProduct, false),
            'attributes' => [
                'type'          => StoreProductType::fromValue($storeProduct->type)->description,
                'title'         => $storeProduct->title,
                'identifier'    => $storeProduct->identifier
            ]
        ];
    }
}
