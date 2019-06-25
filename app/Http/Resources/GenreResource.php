<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
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
            'color'         => $this->color,
            'symbol'        => $this->symbol,
            'description'   => $this->description,
            'nsfw'          => (bool) $this->nsfw
        ];
    }
}
