<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->resource;

        $resource = UserResourceBasic::make($user)->toArray($request);

        // Add additional data to the resource
        $relationships = [
            'relationships' => [
                'badges' => [
                    'data' => BadgeResource::collection($user->badges)
                ]
            ]
        ];

        return array_merge($resource, $relationships);
    }
}
