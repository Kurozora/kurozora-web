<?php

namespace App\Http\Livewire\Manga;

use App\Enums\UserLibraryStatus;
use App\Models\Manga;
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
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * The status of the manga in the auth user's library.
     *
     * @var int $libraryStatus
     */
    public int $libraryStatus;

    /**
     * Prepare the component.
     *
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        $this->manga = $manga;

        // Set library status, else default to "ADD"
        $this->libraryStatus = UserLibrary::firstWhere([
            ['user_id', auth()->user()?->id],
            ['trackable_type', Manga::class],
            ['trackable_id', $manga->id],
        ])?->status ?? -1;
    }

    /**
     * Updates the status of the manga in the auth user's library.
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
            $user->untrack($this->manga);
            $user->unfavorite($this->manga);
//            $user->reminderManga()->detach($this->manga->id);

            // Reset dropdown to "ADD".
            $this->libraryStatus = -1;
        } else {
            $endDate = match ($this->libraryStatus) {
                UserLibraryStatus::Completed => now(),
                default => null
            };

            // Update or create the user library entry.
            UserLibrary::updateOrCreate([
                'user_id'   => $user->id,
                'trackable_type'  => Manga::class,
                'trackable_id'  => $this->manga->id
            ], [
                'status' => $this->libraryStatus,
                'ended_at' => $endDate
            ]);
        }

        // Notify other components of an update in the manga's data.
        $this->emitUp('update-manga');

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.library-button');
    }
}
