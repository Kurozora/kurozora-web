<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\UserSettingResource;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;

class MeSettingsController extends Controller
{
    /**
     * Returns the authenticated user's settings.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        return JSONResult::success([
            'data' => [
                UserSettingResource::make($this->settings())
            ]
        ]);
    }

    /**
     * Updates the authenticated user's settings.
     *
     * @param UpdateSettingsRequest $request
     *
     * @return JsonResponse
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $settings = $this->settings();

        if (array_key_exists('scrobbleThreshold', $data)) {
            $settings->scrobble_threshold = $data['scrobbleThreshold'];
        }

        if ($request->has('discordRichPresenceEnabled')) {
            $settings->discord_rich_presence_enabled = $request->boolean('discordRichPresenceEnabled');
        }

        if (array_key_exists('discordPresenceImage', $data)) {
            $settings->discord_presence_image = (int) $data['discordPresenceImage'];
        }

        if (array_key_exists('discordActivityName', $data)) {
            $settings->discord_activity_name = (int) $data['discordActivityName'];
        }

        if (array_key_exists('ratingStyle', $data)) {
            $settings->rating_style = (int) $data['ratingStyle'];
        }

        $settings->save();

        return JSONResult::success([
            'data' => [
                UserSettingResource::make($settings)
            ]
        ]);
    }

    /**
     * Returns the authenticated user's settings, creating the row when absent.
     *
     * @return UserSetting
     */
    protected function settings(): UserSetting
    {
        return auth()->user()
            ->settings()
            ->firstOrCreate();
    }
}
