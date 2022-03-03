<?php

namespace App\Http\Resources;

use App\Models\AnimeSong;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeSongResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var AnimeSong $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = AnimeSongResourceBasic::make($this->resource)->toArray($request);

        return $resource;
    }
}
