<?php

namespace App\Livewire\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class MoreByStudioSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * Anime parent populated by Livewire's blade-attribute binding when the kind is Anime.
     *
     * @var Anime|null $anime
     */
    public ?Anime $anime = null;

    /**
     * Manga parent populated by Livewire's blade-attribute binding when the kind is Manga.
     *
     * @var Manga|null $manga
     */
    public ?Manga $manga = null;

    /**
     * Game parent populated by Livewire's blade-attribute binding when the kind is Game.
     *
     * @var Game|null $game
     */
    public ?Game $game = null;

    /**
     * The studio whose other works should be surfaced.
     *
     * @var Studio|null $studio
     */
    public ?Studio $studio = null;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param int $kind
     *
     * @return void
     */
    public function mount(int $kind): void
    {
        $this->kind = $kind;

        $parent = $this->parent;
        $translation = $parent->translation;

        $stripped = $parent->withoutRelations()
            ->setRelation('translation', $translation);

        match ($this->kind) {
            UserLibraryKind::Anime => $this->anime = $stripped,
            UserLibraryKind::Manga => $this->manga = $stripped,
            UserLibraryKind::Game  => $this->game = $stripped,
        };
    }

    /**
     * Resolves the active parent model for the current kind.
     *
     * @return Anime|Manga|Game
     */
    public function getParentProperty(): Anime|Manga|Game
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => $this->anime,
            UserLibraryKind::Manga => $this->manga,
            UserLibraryKind::Game  => $this->game,
        };
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Loads the more by studio section.
     *
     * @return Collection
     */
    public function getMoreByStudioProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $relation = match ($this->kind) {
            UserLibraryKind::Anime => $this->studio->anime(),
            UserLibraryKind::Manga => $this->studio->manga(),
            UserLibraryKind::Game  => $this->studio->games(),
        };

        $relation->when($this->studio->tv_rating_id > config('app.tv_rating'), function ($query) {
            $query->withoutGlobalScopes();
        })
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
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
            ->where('model_id', '!=', $this->parent->id)
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT);

        return $relation->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.more-by-studio-section');
    }
}
