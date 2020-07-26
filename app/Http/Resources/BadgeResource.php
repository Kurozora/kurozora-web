<?php

namespace App\Http\Resources;

use App\Badge;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Badge $badge */
        $badge = $this->resource;

        return [
            'id'            => $badge->id,
            'type'          => 'badges',
            'attributes'    => [
                'text'              => $badge->text,
                'text_color'        => $badge->textColor,
                'background_color'  => $badge->backgroundColor,
                'description'       => $badge->description
            ]
        ];
    }
}
