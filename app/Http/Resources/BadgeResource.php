<?php

namespace App\Http\Resources;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Badge $resource
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
        $badge = $this->resource;

        return [
            'id'            => $badge->id,
            'type'          => 'badges',
            'attributes'    => [
                'name'              => $badge->name,
                'description'       => $badge->description,
                'textColor'         => $badge->text_color,
                'backgroundColor'   => $badge->background_color,
                'symbol'            => ImageResource::make($this->resource->symbol_image),
            ]
        ];
    }
}
