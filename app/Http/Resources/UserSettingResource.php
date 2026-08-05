<?php

namespace App\Http\Resources;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var UserSetting $resource
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
            'type' => 'settings',
            'href' => route('api.me.settings', [], false),
            'attributes' => [
                'scrobbleThreshold' => (int) $this->resource->scrobble_threshold,
                'discordRichPresenceEnabled' => (bool) $this->resource->discord_rich_presence_enabled,
                'discordPresenceImage' => (int) $this->resource->discord_presence_image,
                'discordActivityName' => (int) $this->resource->discord_activity_name,
            ],
        ];
    }
}
