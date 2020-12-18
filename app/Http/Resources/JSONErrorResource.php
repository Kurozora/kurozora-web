<?php

namespace App\Http\Resources;

use App\Models\APIError;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JSONErrorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var APIError $apiError */
        $apiError = $this->resource;

        return [
            'id'        => $apiError->id,
            'status'    => $apiError->status,
            'title'     => $apiError->title,
            'detail'    => $apiError->detail,
        ];
    }
}
