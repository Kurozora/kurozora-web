<?php

namespace App\Http\Resources;

use App\Services\AppleMusicService;
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
        $developerToken = app(AppleMusicService::class)->developerToken();

        return [
            'appleMusicDeveloperToken' => $developerToken['token'],
            'appleMusicDeveloperTokenExpiresAt' => $developerToken['expiresAt'],
            'youtubeAPIKey' => config('services.youtube.api_key')
        ];
    }
}
