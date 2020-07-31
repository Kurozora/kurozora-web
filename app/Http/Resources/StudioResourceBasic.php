<?php

namespace App\Http\Resources;

use App\Studio;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResourceBasic extends JsonResource
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

        $founded = $studio->founded;
        if ($founded)
            $founded = $founded->format('Y-m-d');

        return [
            'id'            => $studio->id,
            'type'          => 'studios',
            'href'          => route('api.studios.details', $studio, false),
            'attributes'    => [
                'name'          => $studio->name,
                'logo_url'      => $studio->logo_url,
                'about'         => $studio->about,
                'founded'       => $founded,
                'website_url'   => $studio->website_url
            ]
        ];
    }
}
