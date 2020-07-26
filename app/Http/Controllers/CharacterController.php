<?php

namespace App\Http\Controllers;

use App\Character;
use App\Helpers\JSONResult;
use App\Http\Resources\CharacterResource;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    /**
     * Generate an overview of characters.
     *
     * @return JsonResponse
     */
    public function overview() {
        // Get all characters and format them
        $allCharacters = Character::get()->map(function($character) {
            return CharacterResource::make($character);
        });

        // Show characters in response
        return JSONResult::success(['data' => $allCharacters]);
    }

    /**
     * Shows character details.
     *
     * @param Character $character
     *
     * @return JsonResponse
     */
    public function details(Character $character) {
        // Show character details
        return JSONResult::success([
            'data' => CharacterResource::collection([$character])
        ]);
    }
}
