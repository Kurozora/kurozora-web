<?php

namespace App\Http\Resources;

use App\UserBadge;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceLarge extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $smallResource = UserResourceSmall::make($this->resource)->toArray($request);

        // Add additional data to the small resource
        $bigResource = [
            'badges' => BadgeResource::collection($this->resource->badges)
        ];

        return array_merge($smallResource, $bigResource);
    }
}
