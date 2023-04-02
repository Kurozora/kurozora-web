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
     * @var bool $includePrivateDetails
     */
    private bool $includePrivateDetails = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = UserResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'uuid'              => $this->resource->uuid,
            'attributes'        => [
                'slug'              => $this->resource->slug,
                'profile'           => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Profile)),
                'banner'            => ImageResource::make($this->resource->getFirstMedia(MediaCollection::Banner)),
                'username'          => $this->resource->username,
                'biography'         => $this->resource->biography,
                'biographyHTML'     => $this->resource->biography_html,
                'biographyMarkdown' => $this->resource->biography_markdown,
                'activityStatus'    => $this->resource->getActivityStatus()->description,
                'followerCount'     => $this->resource->followers()->count(),
                'followingCount'    => $this->resource->following()->count(),
                'reputationCount'   => $this->resource->getReputationCount(),
                'joinDate'          => $this->resource->created_at->timestamp,
                'isPro'             => $this->resource->is_pro,
                'isSubscribed'      => $this->resource->is_subscribed,
                'isVerified'        => $this->resource->is_verified,
            ]
        ]);

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        if ($this->includePrivateDetails) {
            $resource = array_merge($resource, [
                'preferredLanguage' => $this->resource->language_id,
                'preferredTVRating' => $this->resource->tv_rating?->name,
                'canChangeUsername' => $this->resource->can_change_username,
            ]);
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
            $isFollowed = $this->resource->followers()->where('user_id', $user->id)->exists();
        }

        return [
            'isFollowed' => $isFollowed
        ];
    }

    /**
     * Enables including private details in the resource.
     *
     * @return UserResourceBasic
     */
    function includePrivateDetails(): self
    {
        $this->includePrivateDetails = true;
        return $this;
    }
}
