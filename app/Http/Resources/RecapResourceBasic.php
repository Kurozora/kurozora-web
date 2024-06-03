<?php

namespace App\Http\Resources;

use App\Models\Recap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecapResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Recap $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = RecapResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'year' => $this->resource->year,
                'month' => $this->resource->month,
                'description' => null,
                'backgroundColor1' => $this->resource->background_color1,
                'backgroundColor2' => $this->resource->background_color2,
                'artwork' => null,
            ]
        ]);
        return $resource;
    }
}
