<?php

namespace App\Http\Resources;

use App\Actor;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Actor $actor */
        $actor = $this->resource;

        return [
            'id'            => $actor->id,
            'type'          => 'actors',
            'href'          => route('actors.details', $actor, false),
            'attributes'    => [
                'first_name'    => $actor->first_name,
                'last_name'     => $actor->last_name,
                'occupation'    => $actor->occupation,
                'image'         => $actor->image
            ]
        ];
    }
}
