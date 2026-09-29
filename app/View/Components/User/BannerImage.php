<?php

namespace App\View\Components\User;

use App\Enums\MediaCollection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BannerImage extends Component
{
    /**
     * The user whose banner is shown.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Whether the banner is shown on the user's profile page.
     *
     * @var bool $onProfile
     */
    public bool $onProfile;

    /**
     * The user's banner image.
     *
     * @var Media|null $bannerImage
     */
    public ?Media $bannerImage;

    /**
     * The URL that re-renders the banner.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     *
     * @param User $user
     * @param bool $onProfile
     */
    public function __construct(User $user, bool $onProfile = false)
    {
        $this->user = $user;
        $this->onProfile = $onProfile;
        $this->bannerImage = $user->getFirstMedia(MediaCollection::Banner);
        $this->refreshUrl = route('profile.section', [$user, 'banner-image'], false);
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.user.banner-image');
    }
}
