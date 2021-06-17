<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use App\Models\UserLibrary;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class LibraryButton extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The status of the anime in the auth user's library.
     *
     * @var int $libraryStatus
     */
    public int $libraryStatus;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime)
    {
        $this->anime = $anime;

        // Set library status, else default to "ADD"
        $this->libraryStatus = UserLibrary::firstWhere([
            ['user_id', Auth::user()?->id],
            ['anime_id', $anime->id],
        ])?->status ?? -1;
    }

    /**
     * Updates the status of the anime in the auth user's library.
     *
     * @return Application|RedirectResponse|Redirector|null
     */
    public function updateLibraryStatus(): Application|RedirectResponse|Redirector|null
    {
        $user = Auth::user();

        // Require user to authenticate if necessary.
        if (empty($user)) {
            return redirect(route('sign-in'));
        }

        // Remove from library
        $user->library()->detach($this->anime->id);

        // If user explicitly asked for removing from library, then also remove from favorites and reminders.
        // Otherwise, update library status.
        if ($this->libraryStatus == -2) {
            $user->favoriteAnime()->detach($this->anime->id);
            $user->reminderAnime()->detach($this->anime->id);

            // Reset dropdown to "ADD".
            $this->libraryStatus = -1;
        } else {
            $user->library()->attach($this->anime->id, ['status' => $this->libraryStatus]);
        }

        // Notify other components of an update in the anime's data
        $this->emitUp('update-anime');

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.library-button');
    }
}
