<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResourceBasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Person $person */
        $person = $this->resource;

        return [
            'id'            => $person->id,
            'type'          => 'people',
            'href'          => route('api.people.details', $person, false),
            'attributes'    => [
                'firstName'         => $person->first_name,
                'lastName'          => $person->last_name,
                'givenName'         => $person->given_name,
                'familyName'        => $person->family_name,
                'alternativeNames'  => $person->alternative_names,
                'about'             => $person->about,
                'imageURL'          => $person->image,
                'websiteURLs'       => $person->website_url,
            ]
        ];
    }
}
