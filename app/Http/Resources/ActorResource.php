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
            'id'    => $actor->id,
            'name'  => $actor->name,
            'role'  => $actor->role,
            'image' => $actor->image
        ];
    }
}
