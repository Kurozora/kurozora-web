<?php

namespace App\View\Components\Platform;

use App\Models\Game;
use App\Models\Platform;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MediaSection extends Component
{
    /**
     * The platform whose releases are shown.
     *
     * @var Platform $platform
     */
    public Platform $platform;

    /**
     * The class of the shown releases.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The title of the section.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The platform's releases.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * Create a new component instance.
     *
     * @param Platform $platform
     * @param string   $type
     */
    public function __construct(Platform $platform, string $type)
    {
        $this->platform = $platform;
        $this->type = $type;
        $this->title = match ($type) {
            Game::class => __('Games'),
        };
        $this->refreshUrl = match ($type) {
            Game::class => route('platforms.section', [$platform, 'games'], false),
        };
        $this->models = $this->loadModels();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->models->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.platform.media-section');
    }

    /**
     * Load the platform's releases.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        $query = match ($this->type) {
            Game::class => $this->platform->games(),
        };

        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->limit(Platform::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }
}
