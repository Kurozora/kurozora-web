<?php

namespace App\Livewire\Song;

use App\Events\SongViewed;
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
        // Call the SongViewed event
        SongViewed::dispatch($song);

        $this->song = $song->load(['media'])
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.song.details');
    }
}
