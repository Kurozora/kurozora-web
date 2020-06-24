<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $anime = (bool) $request->input('anime');
        $limit = $request->input('limit');
        $resource = StudioResourceSmall::make($this)->toArray($request);

        if ($anime)
            $resource = array_merge($resource, ['anime' => AnimeResource::collection($this->anime->take($limit))]);

        return $resource;
    }
}
