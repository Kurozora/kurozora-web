<?php

namespace App\Http\Resources;

use App\Models\Timeout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeoutResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Timeout $resource
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
        $appeal = $this->resource->relationLoaded('appeal')
            ? $this->resource->appeal
            : $this->resource->appeal()->first();

        $resource = [
            'id' => (string) $this->resource->id,
            'type' => 'timeouts',
            'attributes' => [
                'reasonKey' => $this->resource->reason_key->value,
                'reasonLabel' => $this->resource->reason_key->description,
                'note' => $this->resource->note,
                'isPermanent' => $this->resource->is_permanent,
                'expiresAt' => $this->resource->expires_at?->timestamp,
                'issuedAt' => $this->resource->created_at->timestamp,
                'communityGuidelinesURL' => route('kb.guidelines'),
            ],
        ];

        $relationships = [
            'relationships' => [
                'appeal' => [
                    'data' => $appeal !== null
                        ? TimeoutAppealResource::collection([$appeal])
                        : [],
                ],
            ],
        ];

        return array_merge($resource, $relationships);
    }
}
