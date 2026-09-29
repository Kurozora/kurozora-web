<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RelationsSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The kind of the related titles shown.
     *
     * @var int $relatedKind
     */
    public int $relatedKind;

    /**
     * The anime, manga, or game whose relations are shown.
     *
     * @var Anime|Manga|Game $model
     */
    public Anime|Manga|Game $model;

    /**
     * The relations shown in the section.
     *
     * @var Collection $relations
     */
    public Collection $relations;

    /**
     * The title of the section.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The URL of the full relations page.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

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
     * @param int              $relatedKind
     * @param Anime|Manga|Game $model
     */
    public function __construct(int $kind, int $relatedKind, Anime|Manga|Game $model)
    {
        $this->kind = $kind;
        $this->relatedKind = $relatedKind;
        $this->model = $model;
        $this->relations = $this->loadRelations();
        $this->title = match ([$kind, $relatedKind]) {
            [UserLibraryKind::Anime, UserLibraryKind::Anime] => __('Related'),
            [UserLibraryKind::Anime, UserLibraryKind::Manga] => __('Adaptations'),
            [UserLibraryKind::Anime, UserLibraryKind::Game] => __('Games'),
            [UserLibraryKind::Manga, UserLibraryKind::Manga] => __('Related'),
            [UserLibraryKind::Manga, UserLibraryKind::Anime] => __('Adaptations'),
            [UserLibraryKind::Manga, UserLibraryKind::Game] => __('Games'),
            [UserLibraryKind::Game, UserLibraryKind::Game] => __('Related'),
            [UserLibraryKind::Game, UserLibraryKind::Anime] => __('Shows'),
            [UserLibraryKind::Game, UserLibraryKind::Manga] => __('Literatures'),
        };
        $this->seeAllUrl = match ([$kind, $relatedKind]) {
            [UserLibraryKind::Anime, UserLibraryKind::Anime] => route('anime.related-anime', $model),
            [UserLibraryKind::Anime, UserLibraryKind::Manga] => route('anime.related-mangas', $model),
            [UserLibraryKind::Anime, UserLibraryKind::Game] => route('anime.related-games', $model),
            [UserLibraryKind::Manga, UserLibraryKind::Manga] => route('manga.related-mangas', $model),
            [UserLibraryKind::Manga, UserLibraryKind::Anime] => route('manga.related-anime', $model),
            [UserLibraryKind::Manga, UserLibraryKind::Game] => route('manga.related-games', $model),
            [UserLibraryKind::Game, UserLibraryKind::Game] => route('games.related-games', $model),
            [UserLibraryKind::Game, UserLibraryKind::Anime] => route('games.related-anime', $model),
            [UserLibraryKind::Game, UserLibraryKind::Manga] => route('games.related-literatures', $model),
        };
        $section = match ($relatedKind) {
            UserLibraryKind::Anime => 'related-anime',
            UserLibraryKind::Manga => 'related-manga',
            UserLibraryKind::Game => 'related-games',
        };
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, $section], false),
            UserLibraryKind::Manga => route('manga.section', [$model, $section], false),
            UserLibraryKind::Game => route('games.section', [$model, $section], false),
        };
    }

    /**
     * Whether the section has relations to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->relations->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.relations-section');
    }

    /**
     * Loads the related titles of the related kind.
     *
     * @return Collection
     */
    protected function loadRelations(): Collection
    {
        $model = $this->model;

        $relation = match ($this->relatedKind) {
            UserLibraryKind::Anime => $model->animeRelations(),
            UserLibraryKind::Manga => $model->mangaRelations(),
            UserLibraryKind::Game => $model->gameRelations(),
        };

        return $relation
            ->with([
                'related' => function ($query) use ($model) {
                    $model->viewableViaParent($query)
                        ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation'
            ])
            ->limit($this->maximumLimit())
            ->get();
    }

    /**
     * The maximum number of relationships to load for the active kind.
     *
     * @return int
     */
    protected function maximumLimit(): int
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Manga => Manga::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Game => Game::MAXIMUM_RELATIONSHIPS_LIMIT,
        };
    }
}
