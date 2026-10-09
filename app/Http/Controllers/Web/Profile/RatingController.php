<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\ReviewKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\MediaSong;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Support\SearchCriteria;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RatingController extends Controller
{
    /**
     * Show a user's ratings and reviews.
     *
     * @param GetSearchIndexRequest $request
     * @param User                  $user
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request, User $user): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: MediaRating::webSearchFilters(),
            orders: MediaRating::webSearchOrders(),
            searchTypes: $this->ratingKinds(),
        );
        $hasReview = $criteria->filter['has_review']['selected'];
        $rating = $criteria->filter['rating']['selected'];

        $mediaRatings = $user->mediaRatings()
            ->with([
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Character::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Episode::class => function (Builder $query) {
                            $query->with([
                                'anime' => function ($query) {
                                    $query->with(['media']);
                                },
                                'media',
                                'translation',
                            ]);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Person::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Song::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Studio::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        MediaSong::class => function (Builder $query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media']);
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->when($criteria->typeValue !== null, function ($query) use ($criteria) {
                $query->where('model_type', '=', ReviewKind::fromValue($criteria->typeValue)->getMorphClass());
            })
            ->when($hasReview === '1', fn ($query) => $query->whereNotNull('description'))
            ->when($hasReview === '0', fn ($query) => $query->whereNull('description'))
            ->when($rating !== null, function ($query) use ($rating) {
                $query->where('rating', '>=', (float) $rating)
                    ->where('rating', '<', $rating + 0.5);
            });

        foreach ($criteria->orders() as $order) {
            match ($order['column']) {
                'title' => $mediaRatings->orderByModelTitle($order['direction']),
                default => $mediaRatings->orderBy($order['column'], $order['direction']),
            };
        }

        return view('profile.ratings', [
            'user' => $user,
            'criteria' => $criteria,
            'mediaRatings' => $mediaRatings->orderBy('created_at', 'desc')
                ->paginate($criteria->perPage)
                ->withQueryString(),
        ]);
    }

    /**
     * The kinds of rated models keyed by their review kind.
     *
     * @return array
     */
    protected function ratingKinds(): array
    {
        return [
            'all' => __('All'),
            ReviewKind::Anime => 'Anime',
            ReviewKind::Manga => 'Manga',
            ReviewKind::Game => 'Games',
            ReviewKind::Episode => 'Episodes',
            ReviewKind::Song => 'Songs',
            ReviewKind::Character => 'Characters',
            ReviewKind::Person => 'People',
            ReviewKind::Studio => 'Studios',
        ];
    }
}
