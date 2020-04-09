<?php

namespace App\Http\Resources;

use App\FeedMessage;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var FeedMessage $feedMessage */
        $feedMessage = $this->resource;

        return [
            'id'        => $feedMessage->id,
            'user'      => UserResourceSmall::make($feedMessage->user),
            'body'      => $feedMessage->body,
            'replies'   => FeedMessageResource::collection($feedMessage->replies)
        ];
    }
}
