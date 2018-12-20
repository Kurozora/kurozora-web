<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Helpers\JSONResult;
use App\UserLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LibraryController extends Controller
{
    /**
     * Gets the user's library depending on the status
     *
     * @param Request $request
     * @param $userID
     */
    public function getLibrary(Request $request, $userID) {
        // Check if we can retrieve the sessions of this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to view this.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'status'            => 'bail|required|string'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $givenStatus = $request->input('status');

        // Check the status
        $foundStatus = UserLibrary::getStatusFromString($givenStatus);

        if($foundStatus == null)
            (new JSONResult())->setError('The given status is not a valid one.')->show();

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
                [UserLibrary::TABLE_NAME . '.user_id', '=', $userID],
                [UserLibrary::TABLE_NAME . '.status',  '=', $foundStatus]
            ])
            ->get($columnsToSelect);

        (new JSONResult())->setData(['anime' => $animeInfo])->show();
    }

    /**
     * Adds an Anime to the user's library
     *
     * @param Request $request
     * @param $userID
     */
    public function addLibrary(Request $request, $userID) {
        // Check if we can retrieve the sessions of this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to do this.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'anime_id'          => 'bail|required|numeric|exists:anime,id',
            'status'            => 'bail|required|string'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $givenAnimeID = $request->input('anime_id');
        $givenStatus = $request->input('status');

        // Check the status
        $foundStatus = UserLibrary::getStatusFromString($givenStatus);

        if($foundStatus == null)
            (new JSONResult())->setError('The given status is not a valid one.')->show();

        // Check if this user already has the Anime in their library
        $oldLibraryItem = UserLibrary::where([
            ['user_id',     '=',    $userID],
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
                'user_id'   => $userID,
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
     * @param Request $request
     * @param $userID
     */
    public function delLibrary(Request $request, $userID) {
        // Check if we can retrieve the sessions of this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to do this.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'anime_id'          => 'bail|required|numeric|exists:anime,id'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $givenAnimeID = $request->input('anime_id');

        // Find the Anime in their library
        $foundAnime = UserLibrary::where([
            ['user_id',     '=',    $userID],
            ['anime_id',    '=',    $givenAnimeID]
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
