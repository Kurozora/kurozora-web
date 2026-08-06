<?php

namespace App\Http\Resources;

use App\Models\ParentalGuideEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentalGuideEntryResourceIdentity extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var ParentalGuideEntry $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->resource->id,
            'type' => 'parentalguide-entries',
            'href' => '',
        ];
    }
}
