<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserLibraryStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Manga;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MergeLibraryController extends Controller
{
    /**
     * Show the page that resolves a local library against the signed-in user's library.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        return view('merge-library.index', [
            'userLibrary' => $this->userLibrary($request->user()->id),
        ]);
    }

    /**
     * The user's library counts grouped by kind and status.
     *
     * @param int $userId
     *
     * @return Collection
     */
    private function userLibrary(int $userId): Collection
    {
        $userLibrary = UserLibrary::select(['trackable_type', 'status', DB::raw('COUNT(*) as total'), DB::raw('max(updated_at) as updated_at')])
            ->where('user_id', '=', $userId)
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
}
