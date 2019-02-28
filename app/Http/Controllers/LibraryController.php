<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Enums\UserLibraryStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\AddToLibrary;
use App\Http\Requests\DeleteFromLibrary;
use App\Http\Requests\GetLibrary;
use App\User;
use App\UserLibrary;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    /**
     * Gets the user's library depending on the status
     *
     * @param GetLibrary $request
     * @param User $user
     */
    public function getLibrary(GetLibrary $request, User $user) {
        $data = $request->validated();

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        /*
         * Selects the necessary data from the Anime that are ..
         * .. in the user's library, that match the given status
         */
        $columnsToSelect = [
            Anime::TABLE_NAME . '.id',
            Anime::TABLE_NAME . '.title',
            Anime::TABLE_NAME . '.episode_count',
            Anime::TABLE_NAME . '.average_rating',
            Anime::TABLE_NAME . '.cached_poster_thumbnail AS poster_thumbnail',
            Anime::TABLE_NAME . '.cached_background_thumbnail AS background_thumbnail'
        ];

        $animeInfo = DB::table(Anime::TABLE_NAME)
            ->join(UserLibrary::TABLE_NAME, function ($join) {
                $join->on(Anime::TABLE_NAME . '.id', '=', UserLibrary::TABLE_NAME . '.anime_id');
            })
            ->where([
                [UserLibrary::TABLE_NAME . '.user_id', '=', $user->id],
                [UserLibrary::TABLE_NAME . '.status',  '=', $foundStatus]
            ])
            ->get($columnsToSelect);

        (new JSONResult())->setData(['anime' => $animeInfo])->show();
    }

    /**
     * Adds an Anime to the user's library
     *
     * @param AddToLibrary $request
     * @param User $user
     */
    public function addLibrary(AddToLibrary $request, User $user) {
        $data = $request->validated();

        $givenAnimeID = $data['anime_id'];

        // Get the status
        $foundStatus = UserLibraryStatus::getValue($data['status']);

        // Check if this user already has the Anime in their library
        $oldLibraryItem = UserLibrary::where([
            ['user_id',     '=',    $user->id],
            ['anime_id',    '=',    $givenAnimeID]
        ])->first();

        // The user already had the anime in their library, update the status
        if($oldLibraryItem != null) {
            if($oldLibraryItem->status != $foundStatus) {
                $oldLibraryItem->status = $foundStatus;
                $oldLibraryItem->save();
            }
        }
        // Add a new library item
        else {
            UserLibrary::create([
                'user_id'   => $user->id,
                'anime_id'  => $givenAnimeID,
                'status'    => $foundStatus
            ]);
        }

        // Successful response
        (new JSONResult())->show();
    }

    /**
     * Removes an Anime from the user's library
     *
     * @param DeleteFromLibrary $request
     * @param User $user
     */
    public function delLibrary(DeleteFromLibrary $request, User $user) {
        $data = $request->validated();

        // Find the Anime in their library
        $foundAnime = UserLibrary::where([
            ['user_id',     '=',    $user->id],
            ['anime_id',    '=',    $data['anime_id']]
        ])->first();

        // Remove this Anime from their library
        if($foundAnime) {
            $foundAnime->delete();

            // Successful response
            (new JSONResult())->show();
        }

        // Unsuccessful response
        (new JSONResult())->setError('This item is not in your library.')->show();
    }
}
