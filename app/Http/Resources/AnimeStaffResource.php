<?php

namespace App\Http\Resources;

use App\Models\AnimeStaff;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AnimeStaff $animeStaff */
        $animeStaff = $this->resource;

        $resource = [
            'id'            => $animeStaff->id,
            'type'          => 'staff',
            'href'          => route('api.anime.staff', $animeStaff->anime, false),
            'attributes'    => [
                'role'      => $animeStaff->staff_role->only(['name', 'description']),
            ]
        ];

        $relationships = [];

        $relationships = array_merge($relationships, $this->getPersonRelationship());

        $resource = array_merge($resource, ['relationships' => $relationships]);

        return $resource;
    }

    /**
     * Returns the person relationship for the resource.
     *
     * @return array
     */
    protected function getPersonRelationship(): array
    {
        /** @var AnimeStaff $animeStaff */
        $animeStaff = $this->resource;

        return [
            'person' => [
                'href' => route('api.people.details', $animeStaff, false),
                'data' => PersonResourceBasic::make($animeStaff->person),
            ]
        ];
    }
}
