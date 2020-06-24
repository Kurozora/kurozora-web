<?php

namespace App\Http\Resources;

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
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'logo'          => $this->logo,
            'about'         => $this->about,
            'founded'       => $this->founded,
            'website_url'   => $this->website_url
        ];
    }
}
