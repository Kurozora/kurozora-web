<?php

namespace App\Http\Resources;

use App\Models\Character;
use App\Enums\AstrologicalSign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Character $resource
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
        return [
            'id'            => $this->resource->id,
            'type'          => 'characters',
            'href'          => route('api.characters.details', $this->resource, false),
            'attributes'    => [
                'profile'           => ImageResource::make($this->resource->profile_image),
                'name'              => $this->resource->name,
                'nicknames'         => $this->resource->nicknames,
                'about'             => $this->resource->about,
                'debut'             => $this->resource->debut,
                'status'            => $this->resource->status,
                'bloodType'         => $this->resource->blood_type,
                'favoriteFood'      => $this->resource->favorite_food,
                'bust'              => $this->resource->bust,
                'waist'             => $this->resource->waist,
                'hip'               => $this->resource->hip,
                'height'            => $this->resource->height_string,
                'weight'            => $this->resource->weight_string,
                'age'               => $this->resource->age_string,
                'birthdate'         => $this->resource->birthdate,
                'astrologicalSign'  => AstrologicalSign::getDescription($this->resource->astrological_sign) ?: null
            ]
        ];
    }
}
