<?php

namespace App\View\Components\User;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use App\Models\UserLibrary;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class LibrarySection extends Component
{
    /**
     * The user whose library is shown.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The class of the tracked models.
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
     * The URL of the full library.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The most recently updated library entries.
     *
     * @var Collection $library
     */
    public Collection $library;

    /**
     * Create a new component instance.
     *
     * @param User   $user
     * @param string $type
     */
    public function __construct(User $user, string $type)
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime Library'),
            Game::class => __('Games Library'),
            Manga::class => __('Manga Library'),
        };
        $this->seeAllUrl = match ($type) {
            Anime::class => route('profile.anime.library', $user),
            Game::class => route('profile.games.library', $user),
            Manga::class => route('profile.manga.library', $user),
        };
        $this->refreshUrl = match ($type) {
            Anime::class => route('profile.section', [$user, 'anime-library'], false),
            Game::class => route('profile.section', [$user, 'games-library'], false),
            Manga::class => route('profile.section', [$user, 'manga-library'], false),
        };
        $this->library = $this->loadLibrary();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->library->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.user.library-section');
    }

    /**
     * Load the most recently updated library entries.
     *
     * @return Collection
     */
    protected function loadLibrary(): Collection
    {
        return $this->user->whereTracked($this->type)
            ->when(auth()->id() !== $this->user->id, function ($query) {
                $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }
}
