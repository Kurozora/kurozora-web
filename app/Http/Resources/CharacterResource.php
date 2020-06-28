<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Character $character */
        $character = $this->resource;

        return [
            'id'                => $character->id,
            'name'              => $character->name,
            'about'             => $character->about,
            'image'             => $character->image,
            'debut'             => $character->debut,
            'status'            => $character->status,
            'blood_type'        => $character->blood_type,
            'favorite_food'     => $character->favorite_food,
            'bust'              => $character->bust,
            'waist'             => $character->waist,
            'hip'               => $character->hip,
            'height'            => $character->height,
            'age'               => $character->age,
            'birth_day'         => $character->birth_day,
            'birth_month'       => $character->birth_month,
            'astrological_sign' => $character->astrological_sign
        ];
    }
}
