<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var User $resource
     */
    public $resource;

    /**
     * Whether to include private details in the resource.
     *
     * @var bool $shouldIncludeSettings
     */
    private bool $shouldIncludeSettings = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = UserResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'uuid'              => $this->resource->uuid,
            'attributes'        => [
                'slug'              => $this->resource->slug,
                'profile'           => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Profile)),
                'banner'            => ImageResource::make($this->resource->media->firstWhere('collection_name', '=', MediaCollection::Banner)),
                'username'          => $this->resource->username,
                'biography'         => $this->resource->biography,
                'biographyHTML'     => $this->resource->biography_html,
                'biographyMarkdown' => $this->resource->biography_markdown,
                'activityStatus'    => $this->resource->activityStatus->description,
                'followerCount'     => $this->resource->followers_count,
                'followingCount'    => $this->resource->following_count,
                'reputationCount'   => 0, // - TODO: add $this->resource->getReputationCount(),
                'isDeveloper'       => $this->resource->is_developer,
                'isEarlySupporter'  => $this->resource->is_early_supporter,
                'isStaff'           => $this->resource->is_staff,
                'isPro'             => $this->resource->is_pro,
                'isSubscribed'      => $this->resource->is_subscribed,
                'isVerified'        => $this->resource->is_verified,
                'joinDate'          => $this->resource->created_at->timestamp,
                'joinedAt'          => $this->resource->created_at->timestamp,
                'subscribedAt'      => $this->resource->subscribed_at?->timestamp,
            ]
        ]);

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        if ($this->shouldIncludeSettings) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSettings());
        }

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        $followedUser = $this->resource;
        $user = auth()->user();

        $isFollowed = null;
        if ($followedUser->id != $user->id) {
            $isFollowed = (bool) $this->resource->isFollowed;
        }

        return [
            'isFollowed' => $isFollowed
        ];
    }

    /**
     * Returns the user settings for the resource.
     *
     * @return array
     */
    protected function getUserSettings(): array
    {
        return [
            'preferredLanguage' => $this->resource->language_id,
            'preferredTVRating' => $this->resource->tv_rating,
            'preferredTimezone' => $this->resource->timezone,
            'canChangeUsername' => $this->resource->can_change_username,
        ];
    }

    /**
     * Enables including user's settings in the resource.
     *
     * @param bool $include
     * @return UserResourceBasic
     */
    function includingSettings(bool $include = true): self
    {
        $this->shouldIncludeSettings = $include;
        return $this;
    }
}
