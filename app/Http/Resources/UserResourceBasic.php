<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResourceBasic extends JsonResource
{
    /**
     * Whether or not to include private details in the resource.
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
        /** @var User $user */
        $user = $this->resource;

        $purchaseReceipt = $user->receipt;
        $isPro = $purchaseReceipt != null ? (bool) $purchaseReceipt->is_subscribed : false;

        $resource = [
            'id'                => $user->id,
            'type'              => 'users',
            'href'              => route('api.users.profile', $user, false),
            'attributes'        => [
                'username'          => $user->username,
                'biography'         => $user->biography,
                'activityStatus'    => $user->getActivityStatus()->description,
                'profileImageURL'   => $user->profile_image,
                'bannerImageURL'    => $user->banner_image,
                'followerCount'     => $user->getFollowerCount(),
                'followingCount'    => $user->getFollowingCount(),
                'reputationCount'   => $user->getReputationCount(),
                'joinDate'          => $user->created_at->format('Y-m-d'),
                'isPro'             => $isPro
            ]
        ];

        if(Auth::check())
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());

        if($this->includePrivateDetails)
            $resource = array_merge($resource, $this->getPrivateDetails());

        return $resource;
    }

    /**
     * Returns the user specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        /** @var User $followedUser */
        $followedUser = $this->resource;

        /** @var User $user */
        $user = Auth::user();

        $isFollowed = null;
        if($followedUser->id != $user->id)
            $isFollowed = $this->resource->followers()->where('user_id', $user->id)->exists();

        return [
            'isFollowed' => $isFollowed
        ];
    }

    /**
     * Returns private information of the resource.
     *
     * @return array
     */
    protected function getPrivateDetails(): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'private' => [
                'usernameChangeAvailable' => $user->username_change_available,
            ]
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
