<?php

namespace App\Http\Resources;

use App\Character;
use App\Enums\AstrologicalSign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Character $character */
        $character = $this->resource;

        return [
            'id'            => $character->id,
            'type'          => 'characters',
            'href'          => route('api.characters.details', $character, false),
            'attributes'    => [
                'name'              => $character->name,
                'about'             => $character->about,
                'imageURL'          => $character->image,
                'debut'             => $character->debut,
                'status'            => $character->status,
                'bloodType'         => $character->blood_type,
                'favoriteFood'      => $character->favorite_food,
                'bust'              => $character->bust,
                'waist'             => $character->waist,
                'hip'               => $character->hip,
                'height'            => $character->height,
                'age'               => $character->age,
                'birthDay'          => $character->birth_day,
                'birthMonth'        => $character->birth_month,
                'astrologicalSign'  => AstrologicalSign::getDescription($character->astrological_sign)
            ]
        ];
    }
}
