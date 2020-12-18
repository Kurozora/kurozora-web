<?php

namespace App\Http\Resources;

use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResourceBasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Actor $actor */
        $actor = $this->resource;

        return [
            'id'            => $actor->id,
            'type'          => 'actors',
            'href'          => route('api.actors.details', $actor, false),
            'attributes'    => [
                'firstName'    => $actor->first_name,
                'lastName'     => $actor->last_name,
                'occupation'   => $actor->occupation,
                'imageURL'     => $actor->image
            ]
        ];
    }
}
