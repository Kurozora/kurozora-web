<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var array $resource
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
            'appleMusicDeveloperToken' => config('services.apple.client_secret'),
            'youtubeAPIKey' => config('services.youtube.api_key')
        ];
    }
}
