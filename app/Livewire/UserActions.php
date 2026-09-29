<?php

namespace App\Livewire;

use App\Enums\FeedVoteType;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\FeedMessage;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Notifications\NewFollower;
use App\Services\ScrobbleService;
use App\Traits\Livewire\PresentsAlert;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class UserActions extends Component
{
    use PresentsAlert;

    /**
     * Update the signed-in user's library entry for a title.
     *
     * @param string $type
     * @param int    $id
     * @param int    $status
     *
     * @return void
     */
    #[On('library-update')]
    public function updateLibrary(string $type, int $id, int $status): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $model = $this->trackable($type, $id);
        $reminded = null;

        if ($status < 0) {
            DB::transaction(function () use ($user, $model) {
                $user->untrack($model);
                $user->unfavorite($model);
                $user->unremind($model);
                $user->bumpStateVersion();
            });

            $status = -1;
        } else {
            abort_unless(UserLibraryStatus::hasValue($status), 422);

            $previousStatus = $user->library()
                ->where('trackable_type', '=', $model->getMorphClass())
                ->where('trackable_id', '=', $model->id)
                ->value('status');

            $userLibrary = UserLibrary::withoutSyncingToSearch(function () use ($user, $model, $status, $previousStatus, &$reminded) {
                return DB::transaction(function () use ($user, $model, $status, $previousStatus, &$reminded) {
                    $userLibrary = UserLibrary::withTrashed()->updateOrCreate([
                        'user_id' => $user->id,
                        'trackable_type' => $model->getMorphClass(),
                        'trackable_id' => $model->id,
                    ], [
                        'status' => $status,
                        'deleted_at' => null,
                    ]);

                    if (UserLibraryStatus::enablesRemindersByDefault($status)) {
                        if ($previousStatus === null) {
                            $user->remind($model);
                            $reminded = true;
                        }
                    } elseif ($previousStatus === null || UserLibraryStatus::enablesRemindersByDefault($previousStatus)) {
                        $user->unremind($model);
                        $reminded = false;
                    }

                    $user->bumpStateVersion();

                    return $userLibrary;
                });
            });

            $userLibrary->setRelation('trackable', $model);
            $userLibrary->searchable();
        }

        $this->dispatch('library-updated', type: $model->getMorphClass(), id: $model->id, status: $status);
        $this->dispatch(match ($model::class) {
            Anime::class => 'update-anime',
            Game::class => 'update-game',
            Manga::class => 'update-manga',
        }, id: $model->id);

        if ($reminded !== null && $model instanceof Anime) {
            $this->dispatch('title-reminded', type: $model->getMorphClass(), id: $model->id, reminded: $reminded);
        }
    }

    /**
     * Toggle whether the signed-in user has watched an episode.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('episode-watch')]
    public function toggleEpisodeWatched(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $episode = $this->query(Episode::class)->findOrFail($id);

        if ($user->hasWatched($episode)) {
            $user->episodes()->withoutGlobalScopes()->detach($episode);
            $watched = false;
        } else {
            $anime = $episode->anime()->withoutGlobalScopes()
                ->select([Anime::TABLE_NAME . '.id'])
                ->first();

            app(ScrobbleService::class)->ensureTracked($user, $anime);

            $user->episodes()->withoutGlobalScopes()->syncWithoutDetaching([
                $episode->id => UserWatchedEpisode::completedAttributes(),
            ]);
            $watched = true;
        }

        $user->bumpStateVersion();

        $season = $episode->season()->withoutGlobalScopes()->first();

        $this->dispatch('episode-watched', id: $episode->id, watched: $watched);

        if ($season !== null) {
            $this->dispatch('season-watched', id: $season->id, watched: $user->hasWatchedSeason($season));
        }

        $this->dispatch('refresh-up-next-episodes');
        $this->dispatch('refresh-past-episodes');
        $this->dispatch('refresh-up-next-section');
    }

    /**
     * Toggle whether the signed-in user has watched every episode of a season.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('season-watch')]
    public function toggleSeasonWatched(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $season = $this->query(Season::class)->findOrFail($id);
        $episodeIDs = $season->episodes()->pluck('id');

        if ($user->hasWatchedSeason($season)) {
            $user->episodes()->detach($episodeIDs);
            $watched = false;
        } else {
            $anime = $season->anime()->withoutGlobalScopes()
                ->select([Anime::TABLE_NAME . '.id'])
                ->first();

            app(ScrobbleService::class)->ensureTracked($user, $anime);

            $existingIDs = $user->episodes()
                ->whereIn('episode_id', $episodeIDs)
                ->pluck('episode_id');

            $user->episodes()->attach($episodeIDs->diff($existingIDs), UserWatchedEpisode::completedAttributes());

            $user->userWatchedEpisodes()
                ->whereIn('episode_id', $episodeIDs)
                ->whereNull('completed_at')
                ->update(UserWatchedEpisode::completedAttributes());
            $watched = true;
        }

        $user->bumpStateVersion();

        $this->dispatch('season-watched', id: $season->id, watched: $watched);
        $this->dispatch('update-season');
    }

    /**
     * Toggle whether the signed-in user is reminded of a title's releases.
     *
     * @param string $type
     * @param int    $id
     *
     * @return void
     */
    #[On('title-remind')]
    public function remindTitle(string $type, int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $model = $this->trackable($type, $id);
        $isTracking = $this->isTracking($user, $model);
        $wasReminded = $user->hasReminded($model);

        DB::transaction(function () use ($user, $model, $isTracking, $wasReminded) {
            if ($wasReminded) {
                $user->unremind($model);
            } else {
                if (!$isTracking) {
                    $user->track($model, UserLibraryStatus::Planning());
                }

                $user->remind($model);
            }

            $user->bumpStateVersion();
        });

        $this->dispatch('title-reminded', type: $model->getMorphClass(), id: $model->id, reminded: !$wasReminded);

        if (!$wasReminded && !$isTracking) {
            $this->dispatch('library-updated', type: $model->getMorphClass(), id: $model->id, status: UserLibraryStatus::Planning);
        }
    }

    /**
     * Toggle whether the signed-in user has favorited a tracked title.
     *
     * @param string $type
     * @param int    $id
     *
     * @return void
     */
    #[On('title-favorite')]
    public function toggleFavorite(string $type, int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $model = $this->trackable($type, $id);

        if (!$this->isTracking($user, $model)) {
            $this->presentAlert(
                title: __('Are you tracking?'),
                message: match ($model::class) {
                    Anime::class => __('Make sure to add the anime to your library first.'),
                    Manga::class => __('Make sure to add the manga to your library first.'),
                    Game::class => __('Make sure to add the game to your library first.'),
                }
            );
            return;
        }

        $wasFavorited = $model->favoriters()
            ->where('user_id', '=', $user->id)
            ->exists();

        DB::transaction(function () use ($user, $model, $wasFavorited) {
            if ($wasFavorited) {
                $user->unfavorite($model);
            } else {
                $user->favorite($model);
            }

            $user->bumpStateVersion();
        });

        $this->dispatch('title-favorited', type: $model->getMorphClass(), id: $model->id, favorited: !$wasFavorited);
    }

    /**
     * Toggle whether the signed-in user blocks another user.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('user-block')]
    public function toggleBlock(int $id): void
    {
        $authUser = $this->user();

        if ($authUser === null) {
            return;
        }

        $user = User::findOrFail($id);

        if ($user->is($authUser)) {
            return;
        }

        $wasBlocked = $authUser->hasBlocked($user);

        DB::transaction(function () use ($user, $authUser, $wasBlocked) {
            if ($wasBlocked) {
                $authUser->unblock($user);
            } else {
                $authUser->block($user);
            }

            $authUser->bumpStateVersion();
        });

        $this->dispatch('user-blocked', id: $user->id, blocked: !$wasBlocked);
    }

    /**
     * Toggle whether the signed-in user follows another user.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('user-follow')]
    public function toggleFollow(int $id): void
    {
        $authUser = $this->user();

        if ($authUser === null) {
            return;
        }

        $user = User::findOrFail($id);

        if ($user->is($authUser)) {
            return;
        }

        $wasFollowed = $user->isFollowedBy($authUser);

        DB::transaction(function () use ($user, $authUser, $wasFollowed) {
            if ($wasFollowed) {
                $user->followers()->detach($authUser);
            } else {
                $user->followers()->attach($authUser);
            }

            $authUser->bumpStateVersion();
        });

        if (!$wasFollowed) {
            $user->notify(new NewFollower($authUser));
        }

        $this->dispatch('user-followed', id: $user->id, followed: !$wasFollowed);
        $this->dispatch('followers-badge-refresh', followersCount: $wasFollowed ? -1 : 1, userID: $user->id);
    }

    /**
     * Toggle whether the signed-in user hearts a feed message.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('feed-message-heart')]
    public function toggleFeedMessageHeart(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $feedMessage = FeedMessage::findOrFail($id);
        $hearted = $user->toggleHeart($feedMessage) === 1;
        $reactant = $feedMessage->getLoveReactant();
        $count = $reactant instanceof Reactant
            ? $reactant->reactions()
                ->where('reaction_type_id', '=', ReactionType::fromName(FeedVoteType::Heart()->description)->getId())
                ->count()
            : 0;

        $this->dispatch('feed-message-hearted', id: $feedMessage->id, hearted: $hearted, count: $count);
    }

    /**
     * Toggle the signed-in user's plain re-share of a feed message.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('feed-message-reshare')]
    public function toggleFeedMessageReShare(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $feedMessage = FeedMessage::findOrFail($id);
        $reShares = $feedMessage->simpleReShares()
            ->where('user_id', '=', $user->id);

        if ($reShares->exists()) {
            $reShares->delete();
            $reShared = false;
        } else {
            try {
                FeedMessage::createFor($user, [
                    'parent_id' => $feedMessage->id,
                    'content' => '',
                    'is_reshare' => true,
                    'is_reply' => false,
                    'is_nsfw' => false,
                    'is_spoiler' => false,
                ]);
            } catch (AuthorizationException $exception) {
                $this->presentAlert(title: __('Re-share'), message: $exception->getMessage());
                return;
            }

            $reShared = true;
        }

        $this->dispatch('feed-message-reshared', id: $feedMessage->id, reshared: $reShared, count: $feedMessage->reShares()->count());
    }

    /**
     * Quote a feed message with the signed-in user's own words.
     *
     * @param int    $id
     * @param string $content
     *
     * @return void
     */
    #[On('feed-message-quote')]
    public function quoteFeedMessage(int $id, string $content): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        abort_if(mb_strlen($content) > FeedMessage::maxContentLength(), 422);

        $feedMessage = FeedMessage::findOrFail($id);

        try {
            FeedMessage::createFor($user, [
                'parent_id' => $feedMessage->id,
                'content' => $content,
                'is_reshare' => true,
                'is_reply' => false,
                'is_nsfw' => false,
                'is_spoiler' => false,
            ]);
        } catch (AuthorizationException $exception) {
            $this->presentAlert(title: __('Quote'), message: $exception->getMessage());
            return;
        }

        $reShared = $feedMessage->simpleReShares()
            ->where('user_id', '=', $user->id)
            ->exists();

        $this->dispatch('feed-message-reshared', id: $feedMessage->id, reshared: $reShared, count: $feedMessage->reShares()->count());
    }

    /**
     * Replace the content of the signed-in user's own feed message.
     *
     * @param int    $id
     * @param string $content
     *
     * @return void
     */
    #[On('feed-message-edit')]
    public function editFeedMessage(int $id, string $content): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        abort_if(mb_strlen($content) > FeedMessage::maxContentLength(), 422);

        $user->feedMessages()
            ->where('id', '=', $id)
            ->update(['content' => $content]);

        $this->dispatch('feed-message-edited', id: $id, content: $content);
    }

    /**
     * Delete the signed-in user's own feed message.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('feed-message-delete')]
    public function deleteFeedMessage(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $user->feedMessages()
            ->where('id', '=', $id)
            ->delete();

        $this->dispatch('feed-message-deleted', id: $id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.user-actions');
    }

    /**
     * The signed-in user.
     *
     * @return User|null
     */
    protected function user(): ?User
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('sign-in', navigate: true);
        }

        return $user;
    }

    /**
     * The anime, manga, or game with the given morph type and id.
     *
     * @param string $type
     * @param int    $id
     *
     * @return Anime|Manga|Game
     */
    protected function trackable(string $type, int $id): Anime|Manga|Game
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        abort_unless(in_array($class, [Anime::class, Manga::class, Game::class], true), 422);

        return $this->query($class)->findOrFail($id);
    }

    /**
     * Whether the user tracks the given title.
     *
     * @param User             $user
     * @param Anime|Manga|Game $model
     *
     * @return bool
     */
    protected function isTracking(User $user, Anime|Manga|Game $model): bool
    {
        return $user->library()
            ->where('trackable_type', '=', $model->getMorphClass())
            ->where('trackable_id', '=', $model->id)
            ->exists();
    }

    /**
     * A query for the model class that keeps only its soft delete scope.
     *
     * @param string $class
     *
     * @return Builder
     */
    protected function query(string $class): Builder
    {
        $model = new $class;

        return $model->withoutGlobalScopesExceptSoftDeletes($model->newQuery());
    }
}
