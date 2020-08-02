<?php

namespace App\Http\Resources;

use App\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudioResourceBasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
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
                'logoUrl'       => $studio->logo_url,
                'about'         => $studio->about,
                'founded'       => $founded,
                'websiteUrl'    => $studio->website_url
            ]
        ];
    }
}
