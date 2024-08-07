<?php

namespace App\Http\Resources;

use App\Models\SessionAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var SessionAttribute $resource
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
            'type'          => 'platforms',
            'attributes'    => [
                'description'       => $this->resource?->full_platform ?? 'Unknown',
                'systemName'        => $this->resource?->platform ?? 'Unknown',
                'systemVersion'     => $this->resource?->platform_version ?? 'Unknown',
                'deviceVendor'      => $this->resource?->device_vendor ?? 'Unknown',
                'deviceModel'       => $this->resource?->device_model ?? 'Unknown',
            ]
        ];
    }
}
