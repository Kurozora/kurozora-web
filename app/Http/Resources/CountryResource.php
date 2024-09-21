<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Country $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
            'code' => $this->resource->code,
            'iso31663' => $this->resource->iso_3166_3,
        ];
    }
}
