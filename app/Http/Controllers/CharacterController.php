<?php

namespace App\Http\Controllers;

use App\Character;
use App\Helpers\JSONResult;
use App\Http\Resources\CharacterResource;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    /**
     * Generate an overview of genres.
     *
     * @return JsonResponse
     */
    public function overview() {
        // Get all genres and format them
        $allCharacters = Character::get()->map(function($character) {
            return CharacterResource::make($character);
        });

        // Show genres in response
        return JSONResult::success(['characters' => $allCharacters]);
    }

    /**
     * Shows genre details
     *
     * @param \App\Character $character
     *
     * @return JsonResponse
     */
    public function details(Character $character) {
        // Show genre details
        return JSONResult::success([
            'character' => CharacterResource::make($character)
        ]);
    }
}
