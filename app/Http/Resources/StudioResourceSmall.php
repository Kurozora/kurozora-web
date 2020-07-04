<?php

namespace App\Http\Resources;

use App\Studio;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResourceSmall extends JsonResource
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

        return [
            'id'            => $studio->id,
            'name'          => $studio->name,
            'logo_url'      => $studio->logo_url,
            'about'         => $studio->about,
            'founded'       => $studio->founded->format('Y-m-d'),
            'website_url'   => $studio->website_url
        ];
    }
}
