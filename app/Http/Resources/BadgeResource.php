<?php

namespace App\Http\Resources;

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
        return [
            'id'                => $this->id,
            'text'              => $this->text,
            'textColor'         => $this->textColor,
            'backgroundColor'   => $this->backgroundColor,
            'description'       => $this->description
        ];
    }
}
