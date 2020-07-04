<?php

namespace App\Http\Resources;

use App\Studio;
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
        /** @var Studio $studio */
        $studio = $this->resource;

        $anime = (bool) $request->input('anime');
        $limit = $request->input('limit');
        $resource = StudioResourceSmall::make($this)->toArray($request);

        if ($anime)
            $resource = array_merge($resource, ['anime' => AnimeResource::collection($studio->anime->take($limit))]);

        return $resource;
    }
}
