<?php

namespace App\Livewire;

use App\Enums\FeedVoteType;
use App\Enums\ImportBehavior;
use App\Enums\KTheme;
use App\Enums\ParentalGuideReaction;
use App\Enums\ParentalGuideReportReason;
use App\Enums\ReportReason;
use App\Enums\UserLibraryStatus;
use App\Events\Notifications\NotificationDeleted;
use App\Events\Notifications\NotificationRead;
use App\Jobs\ProcessLocalLibraryImport;
use App\Models\Anime;
use App\Models\AppIcon;
use App\Models\AppTheme;
use App\Models\Episode;
use App\Models\FeedMessage;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\ParentalGuideEntry;
use App\Models\Report;
use App\Models\Season;
use App\Models\User;
use App\Models\UserLibrary;
use App\Models\UserWatchedEpisode;
use App\Notifications\NewFollower;
use App\Services\ScrobbleService;
use App\Support\UserLibraryTouch;
use App\Traits\InteractsWithSessions;
use App\Traits\Livewire\PresentsAlert;
use App\Traits\Livewire\PresentsSubscriptionSheet;
use BenSampo\Enum\Rules\EnumValue;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class UserActions extends Component
{
    use InteractsWithSessions;
    use PresentsAlert;
    use PresentsSubscriptionSheet;

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
     * Apply a theme for the visitor.
     *
     * @param string $id
     *
     * @return void
     */
    #[On('theme-get')]
    public function getTheme(string $id): void
    {
        if (!is_numeric($id)) {
            $theme = KTheme::fromValue(strtolower($id));

            $this->dispatch('theme-download', theme: [
                'id' => $theme->value,
                'css' => $theme->toCSS(),
            ]);
            $this->dispatch('theme-changed', id: $theme->value);
            return;
        }

        $user = $this->user();

        if ($user === null) {
            return;
        }

        if (!($user->is_subscribed || $user->is_pro)) {
            $this->presentSubscriptionSheet(
                title: __('Dynamic Themes'),
                message: __('Choose from a range of themes to create a look that reflects your personality and style.'),
                tipJarEnabled: true
            );
            return;
        }

        $appTheme = AppTheme::findOrFail($id);

        $appTheme->update([
            'download_count' => $appTheme->download_count + 1
        ]);

        $this->dispatch('theme-download', theme: [
            'id' => $appTheme->id,
            'css' => $appTheme->toCSS(),
        ]);
        $this->dispatch('theme-changed', id: (string) $appTheme->id);
    }

    /**
     * Scrape the episodes of a season's anime on a local machine.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('episodes-update')]
    public function updateEpisodes(int $id): void
    {
        if (!app()->isLocal()) {
            return;
        }

        $season = $this->query(Season::class)->findOrFail($id);
        $anime = $season->anime()->withoutGlobalScopes()->first();

        if ($anime?->tvdb_id === null) {
            return;
        }

        Artisan::call('scrape:tvdb_episode', ['tvdbID' => $anime->tvdb_id]);
        $this->dispatch('update-season');
    }

    /**
     * Toggle the signed-in user's helpfulness vote on a review.
     *
     * @param int    $id
     * @param string $direction
     *
     * @return void
     */
    #[On('review-vote')]
    public function voteOnReview(int $id, string $direction): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()->findOrFail($id);

        if ((int) $mediaRating->user_id === $user->id) {
            return;
        }

        $vote = $this->vote($user, $mediaRating, $direction);

        $this->dispatch('review-voted', id: $mediaRating->id, helpful: $vote['helpful'], helpfulCount: $vote['helpfulCount'], unhelpfulCount: $vote['unhelpfulCount']);
    }

    /**
     * Toggle whether a review holds the community pick slot of its title.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('review-elevate')]
    public function elevateReview(int $id): void
    {
        $user = $this->user();

        if ($user === null || !$user->can('elevateMediaRating')) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()->findOrFail($id);

        if (trim((string) $mediaRating->description) === '') {
            return;
        }

        $elevated = !$mediaRating->is_elevated;

        if ($elevated) {
            MediaRating::withoutGlobalScopes()
                ->where('model_type', '=', $mediaRating->model_type)
                ->where('model_id', '=', $mediaRating->model_id)
                ->where('is_elevated', '=', true)
                ->update([
                    'is_elevated' => false,
                    'elevated_at' => null,
                    'elevated_by_user_id' => null,
                ]);
        }

        $mediaRating->update([
            'is_elevated' => $elevated,
            'elevated_at' => $elevated ? now() : null,
            'elevated_by_user_id' => $elevated ? $user->id : null,
        ]);

        $this->dispatch('review-elevated', id: $mediaRating->id, elevated: $elevated);
    }

    /**
     * Delete a review the signed-in user wrote or moderates.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('review-delete')]
    public function deleteReview(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()->findOrFail($id);

        if ((int) $mediaRating->user_id !== $user->id && !$user->hasRole(['superAdmin', 'admin'])) {
            return;
        }

        $mediaRating->delete();

        UserLibraryTouch::touch($mediaRating->user_id, $mediaRating->model_type, [$mediaRating->model_id]);

        $this->dispatch('review-deleted', id: $mediaRating->id);
    }

    /**
     * File the signed-in user's report of a review.
     *
     * @param int    $id
     * @param string $reason
     * @param string $details
     *
     * @return void
     */
    #[On('review-report')]
    public function reportReview(int $id, string $reason, string $details): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $mediaRating = MediaRating::withoutGlobalScopes()->findOrFail($id);
        $validator = Validator::make(['reason' => $reason, 'details' => $details], [
            'reason' => ['bail', 'required', 'string', Rule::in(ReportReason::offeredForReview())],
            'details' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reason,' . ReportReason::Other],
        ]);

        if ($validator->fails()) {
            $this->dispatch('review-report-failed', id: $mediaRating->id, message: $validator->errors()->first());
            return;
        }

        $this->report($user, $mediaRating, $reason, $details);

        $this->dispatch('review-reported', id: $mediaRating->id);
    }

    /**
     * Toggle the signed-in user's helpfulness vote on a parental guide entry.
     *
     * @param int    $id
     * @param string $direction
     *
     * @return void
     */
    #[On('parental-guide-vote')]
    public function voteOnParentalGuideEntry(int $id, string $direction): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $entry = ParentalGuideEntry::findOrFail($id);
        $vote = $this->vote($user, $entry, $direction);

        $this->dispatch('parental-guide-voted', id: $entry->id, helpful: $vote['helpful'], helpfulCount: $vote['helpfulCount'], unhelpfulCount: $vote['unhelpfulCount']);
    }

    /**
     * Delete a parental guide entry the signed-in user wrote or moderates.
     *
     * @param int $id
     *
     * @return void
     */
    #[On('parental-guide-delete')]
    public function deleteParentalGuideEntry(int $id): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $entry = ParentalGuideEntry::findOrFail($id);

        if (!$user->can('delete', $entry)) {
            return;
        }

        $entry->delete();

        $this->dispatch('parental-guide-deleted', id: $entry->id);
        $this->dispatch('parental-guide-updated');
    }

    /**
     * File the signed-in user's report of a parental guide entry.
     *
     * @param int    $id
     * @param string $reason
     * @param string $details
     *
     * @return void
     */
    #[On('parental-guide-report')]
    public function reportParentalGuideEntry(int $id, string $reason, string $details): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $entry = ParentalGuideEntry::findOrFail($id);
        $validator = Validator::make(['reason' => $reason, 'details' => $details], [
            'reason' => ['bail', 'required', 'string', new EnumValue(ParentalGuideReportReason::class, false)],
            'details' => ['bail', 'nullable', 'string', 'max:1000', 'required_if:reason,' . ParentalGuideReportReason::Other],
        ]);

        if ($validator->fails()) {
            $this->dispatch('parental-guide-report-failed', id: $entry->id, message: $validator->errors()->first());
            return;
        }

        $entry->reports()->create([
            'user_id' => $user->id,
            'reason_key' => $reason,
            'details' => $details !== '' ? $details : null,
        ]);

        $this->dispatch('parental-guide-reported', id: $entry->id);
    }

    /**
     * Apply an app icon the signed-in user is allowed to use.
     *
     * @param string $name
     *
     * @return void
     */
    #[On('app-icon-set')]
    public function setAppIcon(string $name): void
    {
        $appIcon = AppIcon::find($name);

        if ($appIcon === null) {
            return;
        }

        if ($appIcon->isPremium()) {
            $user = $this->user();

            if ($user === null) {
                return;
            }

            if (!($user->is_subscribed || $user->is_pro)) {
                $this->presentSubscriptionSheet(
                    title: __('Stylish App Icons'),
                    message: __('Make your home screen stand out with premium and limited time app icons.'),
                    tipJarEnabled: true,
                );
                return;
            }
        }

        $this->dispatch('app-icon-changed', appIcon: [
            'name' => $appIcon->name,
            'url' => $appIcon->getImage(),
        ]);
    }

    /**
     * Mark the signed-in user's notifications as read or unread.
     *
     * @param array $ids
     * @param bool  $read
     *
     * @return void
     */
    #[On('notifications-read')]
    public function setNotificationsRead(array $ids, bool $read): void
    {
        $user = $this->user();

        if ($user === null || empty($ids)) {
            return;
        }

        DB::transaction(function () use ($user, $ids, $read) {
            $query = $user->notifications()->whereIn('id', $ids);

            if ($read) {
                $query->whereNull('read_at')->update(['read_at' => now()]);
            } else {
                $query->whereNotNull('read_at')->update(['read_at' => null]);
            }
        });

        broadcast(new NotificationRead($user->id, array_values($ids), $read))->toOthers();

        $this->dispatch('notifications-updated');
    }

    /**
     * Delete the signed-in user's notifications.
     *
     * @param array $ids
     *
     * @return void
     */
    #[On('notifications-delete')]
    public function deleteNotifications(array $ids): void
    {
        $user = $this->user();

        if ($user === null || empty($ids)) {
            return;
        }

        DB::transaction(function () use ($user, $ids) {
            $user->notifications()->whereIn('id', $ids)->delete();
        });

        broadcast(new NotificationDeleted($user->id, array_values($ids)))->toOthers();

        $this->dispatch('notifications-updated');
    }

    /**
     * Sign the signed-in user out of other sessions after confirming the password.
     *
     * @param array  $keys
     * @param bool   $all
     * @param string $password
     *
     * @return void
     */
    #[On('sessions-sign-out')]
    public function signOutOtherSessions(array $keys, bool $all, string $password): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        try {
            if ($all) {
                $this->signOutAllOtherSessions($password);
            } else {
                $this->signOutSessions($keys, $password);
            }
        } catch (ValidationException $exception) {
            $this->dispatch('sessions-sign-out-failed', message: collect($exception->errors())->flatten()->first());
            return;
        }

        $this->dispatch('sessions-signed-out');
    }

    /**
     * Merge the local library into the signed-in user's library.
     *
     * @param string $library
     *
     * @return void
     */
    #[On('local-library-merge')]
    public function mergeLocalLibrary(string $library): void
    {
        $this->importLocalLibrary($library, ImportBehavior::Merge());
    }

    /**
     * Replace the signed-in user's library with the local library.
     *
     * @param string $library
     *
     * @return void
     */
    #[On('local-library-overwrite')]
    public function overwriteLocalLibrary(string $library): void
    {
        $this->importLocalLibrary($library, ImportBehavior::Overwrite());
    }

    /**
     * Leave the merge page once the local library has been cleared.
     *
     * @param bool $merging
     *
     * @return void
     */
    #[On('local-library-cleared')]
    public function finishLocalLibraryMerge(bool $merging): void
    {
        if ($merging) {
            session()->flash('success', __('Local Library import is in progress.'));
        }

        $this->leaveMergeLibrary();
    }

    /**
     * Go to the page the user intended to visit before merging.
     *
     * @return void
     */
    #[On('local-library-empty')]
    public function leaveMergeLibrary(): void
    {
        $this->redirect(session()->pull('url.intended', route('home')), navigate: true);
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
     * Queue the local library import and ask the page to clear the local library.
     *
     * @param string         $library
     * @param ImportBehavior $behavior
     *
     * @return void
     */
    protected function importLocalLibrary(string $library, ImportBehavior $behavior): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        if (empty(json_decode($library))) {
            $this->leaveMergeLibrary();
            return;
        }

        dispatch(new ProcessLocalLibraryImport($user, $library, $behavior));

        $this->dispatch('clear-local-library');
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

    /**
     * Toggle a helpfulness vote and predict the counts the lockup shows next.
     *
     * @param User                           $user
     * @param MediaRating|ParentalGuideEntry $reactable
     * @param string                         $direction
     *
     * @return array
     */
    protected function vote(User $user, MediaRating|ParentalGuideEntry $reactable, string $direction): array
    {
        if ($reactable->isNotRegisteredAsLoveReactant()) {
            $reactable->registerAsLoveReactant();
            $reactable->refresh();
        }

        $reactable->load($reactable::lockupEagerLoads($user));

        $current = $user->getHelpfulnessFor($reactable);
        $oldHelpful = $current === null ? null : $current->is(ParentalGuideReaction::Helpful());
        $tappedHelpful = match ($direction) {
            'helpful' => true,
            'unhelpful' => false,
            default => null,
        };
        $predicted = $oldHelpful === $tappedHelpful ? null : $tappedHelpful;
        $helpfulCount = (int) $reactable->helpful_count;
        $unhelpfulCount = (int) $reactable->unhelpful_count;

        if ($oldHelpful !== $predicted) {
            if ($oldHelpful === true) {
                $helpfulCount = max(0, $helpfulCount - 1);
            } elseif ($oldHelpful === false) {
                $unhelpfulCount = max(0, $unhelpfulCount - 1);
            }

            if ($predicted === true) {
                $helpfulCount++;
            } elseif ($predicted === false) {
                $unhelpfulCount++;
            }
        }

        $user->setHelpfulness($reactable, match ($predicted) {
            true => ParentalGuideReaction::Helpful(),
            false => ParentalGuideReaction::Unhelpful(),
            default => null,
        });

        return [
            'helpful' => $predicted,
            'helpfulCount' => $helpfulCount,
            'unhelpfulCount' => $unhelpfulCount,
        ];
    }

    /**
     * File one report per user on a review that isn't their own.
     *
     * @param User        $user
     * @param MediaRating $mediaRating
     * @param string      $reason
     * @param string      $details
     *
     * @return void
     */
    protected function report(User $user, MediaRating $mediaRating, string $reason, string $details): void
    {
        if ((int) $mediaRating->user_id === $user->id) {
            return;
        }

        $alreadyReported = Report::where('reportable_type', '=', $mediaRating->getMorphClass())
            ->where('reportable_id', '=', $mediaRating->getKey())
            ->where('user_id', '=', $user->id)
            ->exists();

        if ($alreadyReported) {
            return;
        }

        Report::create([
            'reportable_type' => $mediaRating->getMorphClass(),
            'reportable_id' => $mediaRating->getKey(),
            'user_id' => $user->id,
            'reason_key' => $reason,
            'details' => $details !== '' ? $details : null,
        ]);
    }
}
