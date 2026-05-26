<?php

namespace App\Http\Resources;

use App\Models\TimeoutAppeal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeoutAppealResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var TimeoutAppeal $resource
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
            'type' => 'timeout-appeals',
            'attributes' => [
                'message' => $this->resource->message,
                'createdAt' => $this->resource->created_at->timestamp,
                'updatedAt' => $this->resource->updated_at->timestamp,
            ],
        ];
    }
}
