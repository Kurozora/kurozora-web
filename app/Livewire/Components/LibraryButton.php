<?php

namespace App\Livewire\Components;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class LibraryButton extends Component
{
    /**
     * The object containing the model data.
     *
     * @var Model $model
     */
    public Model $model;

    /**
     * The status of the model in the auth user's library.
     *
     * @var int $libraryStatus
     */
    public int $libraryStatus;

    /**
     * Prepare the component.
     *
     * @param Model $model
     * @return void
     */
    public function mount(Model $model): void
    {
        $this->model = $model;

        // Set library status, else default to "ADD"
        if (auth()->check()) {
            $this->libraryStatus = $model->library->first()?->status ?? -1;
        } else {
            $this->libraryStatus = -1;
        }
    }

    /**
     * Updates the status of the model in the auth user's library.
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
        if ($this->libraryStatus < 0) {
            $user->untrack($this->model);
            $user->unfavorite($this->model);

            if ($this->model->getMorphClass() == Anime::class) {
                $user->reminderAnime()->detach($this->model->id);
            }

            // Reset dropdown to "ADD".
            $this->libraryStatus = -1;
        } else {
            // Update or create the user library entry.
            UserLibrary::updateOrCreate([
                'user_id' => $user->id,
                'trackable_type' => $this->model->getMorphClass(),
                'trackable_id' => $this->model->id
            ], [
                'status' => $this->libraryStatus,
            ]);
        }

        // Notify other components of an update in the model's data.
        $eventName = match($this->model->getMorphClass()) {
            Anime::class => 'update-anime',
            Game::class => 'update-game',
            Manga::class => 'update-manga',
        };
        $this->dispatch($eventName, id: $this->model->id);

        return null;
    }

    /**
     * Get the available user library status for the model.
     *
     * @return array
     */
    public function getUserLibraryStatusProperty(): array
    {
        return match ($this->model->getMorphClass()) {
            Anime::class => UserLibraryStatus::asAnimeSelectArray(),
            Game::class => UserLibraryStatus::asGameSelectArray(),
            Manga::class => UserLibraryStatus::asMangaSelectArray(),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.library-button');
    }
}
