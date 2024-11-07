<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var User $resource
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
        return [
            'id'        => (string) $this->resource->id,
            'type'      => 'users',
            'href'      => route('api.users.profile', $this->resource, false),
        ];
    }
}
