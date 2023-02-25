<?php

namespace App\Http\Livewire\Game;

use App\Enums\UserLibraryStatus;
use App\Models\Game;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class LibraryButton extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The status of the game in the auth user's library.
     *
     * @var int $libraryStatus
     */
    public int $libraryStatus;

    /**
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        $this->game = $game;

        // Set library status, else default to "ADD"
        $this->libraryStatus = UserLibrary::firstWhere([
            ['user_id', auth()->user()?->id],
            ['trackable_type', $game->getMorphClass()],
            ['trackable_id', $game->id],
        ])?->status ?? -1;
    }

    /**
     * Updates the status of the game in the auth user's library.
     *
     * @return Application|RedirectResponse|Redirector|null
     */
    public function updateLibraryStatus(): Application|RedirectResponse|Redirector|null
    {
        $user = auth()->user();

        // Require user to authenticate if necessary.
        if (empty($user)) {
            return redirect(route('sign-in'));
        }

        // If user explicitly asked for removing from library, then also remove from favorites and reminders.
        if ($this->libraryStatus == -2) {
            $user->untrack($this->game);
            $user->unfavorite($this->game);
//            $user->reminderGame()->detach($this->game->id);

            // Reset dropdown to "ADD".
            $this->libraryStatus = -1;
        } else {
            $endDate = match ($this->libraryStatus) {
                UserLibraryStatus::Completed => now(),
                default => null
            };

            // Update or create the user library entry.
            UserLibrary::updateOrCreate([
                'user_id' => $user->id,
                'trackable_type' => Game::class,
                'trackable_id' => $this->game->id
            ], [
                'status' => $this->libraryStatus,
                'ended_at' => $endDate
            ]);
        }

        // Notify other components of an update in the game's data.
        $this->emitUp('update-game');

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.library-button');
    }
}
