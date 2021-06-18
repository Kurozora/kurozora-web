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
        return [
            'id'            => $this->resource->id,
            'type'          => 'people',
            'href'          => route('api.people.details', $this->resource, false),
            'attributes'    => [
                'firstName'         => $this->resource->first_name,
                'lastName'          => $this->resource->last_name,
                'givenName'         => $this->resource->given_name,
                'familyName'        => $this->resource->family_name,
                'alternativeNames'  => $this->resource->alternative_names,
                'about'             => $this->resource->about,
                'imageURL'          => $this->resource->image,
                'websiteURLs'       => $this->resource->website_urls,
            ]
        ];
    }
}
