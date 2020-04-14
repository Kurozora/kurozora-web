<?php

namespace App\Rules;

use App\Anime;
use App\AnimeEpisode;
use App\AnimeSeason;
use App\User;
use Illuminate\Contracts\Validation\Rule;

class ValidateAnimeInLibrary implements Rule
{
	/**
	 * The user whose library will be used to check if the anime exists.
	 *
	 * @var \App\User
	 */
	public User $user;

	/**
	 * The episode used to determine to which anime it belongs.
	 *
	 * @var \App\AnimeEpisode
	 */
	public AnimeEpisode $episode;

	/**
	 * The anime to which the episode belongs.
	 *
	 * @var \App\Anime
	 */
	public Anime $anime;

	/**
	 * Create a new rule instance.
	 *
	 * @param \App\User $user
	 * @param \App\AnimeEpisode $episode
	 * @return void
	 */
    public function __construct($user, $episode)
    {
        $this->user = $user;
        $this->episode = $episode;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
	    if (!$this->user instanceof User) return false;
	    if (!$this->episode instanceof AnimeEpisode) return false;

	    // Get season to which the episode belongs to.
	    $season = AnimeSeason::where('id', $this->episode->season_id)->first();

	    // Get the anime to which the season belongs to.
	    $this->anime = $season->anime()->first();

	    // Check whether the Anime exists in user's library
	    return $this->user->isTracking($this->anime);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Add ' . $this->anime->title . ' to your library to be able to mark its episodes.';
    }
}
