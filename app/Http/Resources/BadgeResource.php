<?php

namespace App\Http\Resources;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Badge $badge */
        $badge = $this->resource;

        return [
            'id'            => $badge->id,
            'type'          => 'badges',
            'attributes'    => [
                'name'              => $badge->text,
                'textColor'         => $badge->textColor,
                'backgroundColor'   => $badge->backgroundColor,
                'description'       => $badge->description
            ]
        ];
    }
}
