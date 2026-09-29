<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MoreByStudioSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The anime, manga, or game being viewed.
     *
     * @var Anime|Manga|Game $model
     */
    public Anime|Manga|Game $model;

    /**
     * The studio whose other works are shown.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * The studio's other works shown in the section.
     *
     * @var Collection $moreByStudio
     */
    public Collection $moreByStudio;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create the component.
     *
     * @param int              $kind
     * @param Anime|Manga|Game $model
     * @param Studio           $studio
     */
    public function __construct(int $kind, Anime|Manga|Game $model, Studio $studio)
    {
        $this->kind = $kind;
        $this->model = $model;
        $this->studio = $studio;
        $this->moreByStudio = $this->loadMoreByStudio();
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, 'more-by-studio', 'studio' => $studio->id], false),
            UserLibraryKind::Manga => route('manga.section', [$model, 'more-by-studio', 'studio' => $studio->id], false),
            UserLibraryKind::Game => route('games.section', [$model, 'more-by-studio', 'studio' => $studio->id], false),
        };
    }

    /**
     * Whether the studio has other works to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->moreByStudio->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.more-by-studio-section');
    }

    /**
     * Loads the studio's other works of the active kind.
     *
     * @return Collection
     */
    protected function loadMoreByStudio(): Collection
    {
        $relation = match ($this->kind) {
            UserLibraryKind::Anime => $this->studio->anime(),
            UserLibraryKind::Manga => $this->studio->manga(),
            UserLibraryKind::Game => $this->studio->games(),
        };

        $relation->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);

                if ($this->kind === UserLibraryKind::Anime) {
                    $query->withExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
                }
            })
            ->where('model_id', '!=', $this->model->id)
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);

        return $relation->get();
    }
}
