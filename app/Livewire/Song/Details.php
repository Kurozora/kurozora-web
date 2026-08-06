<?php

namespace App\Livewire\Song;

use App\Events\ModelViewed;
use App\Models\MediaRating;
use App\Models\Song;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

    /**
     * The object containing the song data.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

    /**
     * Whether to show the share popup to the user.
     *
     * @var bool $showSharePopup
     */
    public bool $showSharePopup = false;

    /**
     * Prepare the component.
     *
     * @param Song $song
     * @return void
     */
    public function mount(Song $song): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($song, request()->ip());

        $locales = array_values(array_unique(array_filter([
            'ja',
            app()->getLocale(),
            config('app.fallback_locale'),
        ])));

        $this->song = $song->load(['media', 'translations' => function ($query) use ($locales) {
            $query->with('language')
                ->whereIn('locale', $locales);
        }])
            ->when(auth()->user(), function ($query, $user) use ($song) {
                return $song->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            }, function() use ($song) {
                return $song;
            });

        if (!auth()->check()) {
            $this->song->setRelation('mediaRatings', collect());
        }

        $this->userRating = $song->mediaRatings;
    }

    /**
     * The external streaming links for the song, keyed by service.
     *
     * @return array<string, string|null>
     */
    public function getMusicLinksProperty(): array
    {
        return [
            'amazon' => $this->song->amazon_id ? config('services.amazon.music.albums') . $this->song->amazon_id : null,
            'deezer' => $this->song->deezer_id ? config('services.deezer.track') . $this->song->deezer_id : null,
            'spotify' => $this->song->spotify_id ? config('services.spotify.track') . $this->song->spotify_id : null,
            'youtube' => $this->song->youtube_id ? config('services.youtube.music.watch') . $this->song->youtube_id : null,
        ];
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.song.details');
    }
}
