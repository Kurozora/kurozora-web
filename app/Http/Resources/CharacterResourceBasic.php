<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Character;
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
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = CharacterResourceIdentity::make($this->resource)->toArray($request);
        return array_merge($resource, [
            'attributes'    => [
                'slug'              => $this->resource->slug,
                'profile'           => MediaResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Profile)),
                'name'              => $this->resource->name,
                'nicknames'         => $this->resource->nicknames,
                'about'             => $this->resource->about,
                'shortDescription'  => $this->resource->short_description,
                'debut'             => $this->resource->debut,
                'status'            => $this->resource->status?->description,
                'bloodType'         => $this->resource->blood_type,
                'favoriteFood'      => $this->resource->favorite_food,
                'bust'              => $this->resource->bust,
                'waist'             => $this->resource->waist,
                'hip'               => $this->resource->hip,
                'height'            => $this->resource->height_string,
                'weight'            => $this->resource->weight_string,
                'age'               => $this->resource->age_string,
                'birthdate'         => $this->resource->birthdate,
                'astrologicalSign'  => $this->resource->astrological_sign?->description,
            ]
        ]);
    }
}
