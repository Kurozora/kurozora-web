<?php

namespace App\Livewire;

use App\Enums\ImportBehavior;
use App\Enums\UserLibraryStatus;
use App\Jobs\ProcessLocalLibraryImport;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class MergeLibrary extends Component
{
    /**
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The selected popup type.
     *
     * @var string $selectedPopupType
     */
    public string $selectedPopupType = '';

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    #[Locked]
    public array $popupData = [
        'title' => '',
        'message' => '',
    ];

    /**
     * List of acceptable popup types.
     *
     * @var array|string[]
     */
    private array $popupTypes = [
        'local',
        'kurozora',
        'merge'
    ];

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'local-library-cleared' => 'finishMerge',
        'local-library-empty'   => 'goToHome',
    ];

    /**
     * Return the user's library statistics.
     *
     * @return Collection
     */
    function getUserLibraryProperty(): Collection
    {
        $userLibrary = UserLibrary::select(['trackable_type', 'status', DB::raw('COUNT(*) as total'), DB::raw('max(updated_at) as updated_at')])
            ->where('user_id', '=', auth()->id())
            ->groupBy(['trackable_type', 'status'])
            ->orderBy('status')
            ->get();

        return collect($userLibrary)
            ->groupBy('trackable_type')
            ->map(function ($item) {
                return $item->mapWithKeys(function ($library) {
                    $libraryStatus = match($library->trackable_type) {
                        Game::class => UserLibraryStatus::getGameDescription($library->status),
                        Manga::class => UserLibraryStatus::getMangaDescription($library->status),
                        default => UserLibraryStatus::getAnimeDescription($library->status)
                    };

                    return [
                        $libraryStatus => [
                            'total' => $library->total,
                            'updated_at' => $library->updated_at,
                        ]
                    ];
                });
            });
    }

    /**
     * Merge Local Library with Kurozora Library.
     *
     * @param string $jsonString
     *
     * @return Redirector|void
     */
    public function mergeLibrary(string $jsonString)
    {
        if (empty(json_decode($jsonString))) {
            return $this->goToHome();
        }

        dispatch(new ProcessLocalLibraryImport(auth()->user(), $jsonString, ImportBehavior::Merge()));

        $this->dispatch('clear-local-library');
    }

    /**
     * Overwrite Kurozora Library with Local Library.
     *
     * @param string $jsonString
     *
     * @return Redirector|void
     */
    public function overwriteLibrary(string $jsonString)
    {
        if (empty(json_decode($jsonString))) {
            return $this->goToHome();
        }

        dispatch(new ProcessLocalLibraryImport(auth()->user(), $jsonString, ImportBehavior::Overwrite()));

        $this->dispatch('clear-local-library');
    }

    /**
     * Finish the merging process.
     *
     * @param bool $merging
     *
     * @return Redirector
     */
    public function finishMerge(bool $merging): Redirector
    {
        if ($merging) {
            session()->flash('success', __('Local Library import is in progress.'));
        }

        return $this->goToHome();
    }

    /**
     * Go to home page.
     *
     * @return Redirector
     */
    public function goToHome(): Redirector
    {
        return redirect()->intended();
    }

    /**
     * Toggles the popup if the given type is accepted.
     *
     * @param string|null $type
     * @return void
     */
    public function togglePopupFor(?string $type): void
    {
        if (!is_string($type) && !in_array($type, $this->popupTypes)) {
            return;
        }

        $this->selectedPopupType = $type;
        $this->popupData = match($type) {
            'local' => [
                'title' => __('Keep Local Library'),
                'message' => __('Selecting this option will overwrite your :x Library with the data from your Local Library. Your Local Library will be erased after that.', ['x' => config('app.name')])
            ],
            'kurozora' => [
                'title' => __('Keep :x Library', ['x' => config('app.name')]),
                'message' => __('Selecting this option will erase your Local Library while preserving your :x Library.', ['x' => config('app.name')])
            ],
            'merge' => [
                'title' => __('Merge Both Libraries'),
                'message' => __('Selecting this option will merge your Local Library with your :x Library. Your Local Library will be erased after that.', ['x' => config('app.name')])
            ],
            default => [
                'title' => '',
                'message' => ''
            ]
        };
        $this->showPopup = true;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.merge-library');
    }
}
