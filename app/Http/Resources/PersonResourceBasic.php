<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Person $resource
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
        $resource = PersonResourceIdentity::make($this->resource)->toArray($request);
        return array_merge($resource, [
            'attributes'    => [
                'slug'              => $this->resource->slug,
                'profile'           => ImageResource::make($this->resource->profile_image),
                'fullName'          => $this->resource->full_name,
                'fullGivenName'     => $this->resource->full_given_name,
                'alternativeNames'  => $this->resource->alternative_names,
                'age'               => $this->resource->age_string,
                'birthdate'         => $this->resource->birthdate?->timestamp,
                'about'             => $this->resource->about,
                'websiteURLs'       => $this->resource->website_urls,
                'astrologicalSign'  => $this->resource->astrological_sign?->description,
            ]
        ]);
    }
}
