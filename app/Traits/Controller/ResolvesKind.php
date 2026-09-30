<?php

namespace App\Traits\Controller;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;

trait ResolvesKind
{
    /**
     * The model class of a library kind.
     *
     * @param int $kind
     *
     * @return string
     */
    protected function modelClass(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
        };
    }

    /**
     * The media type kind of a library kind.
     *
     * @param int $kind
     *
     * @return string
     */
    protected function mediaTypeKind(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Anime => 'anime',
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game => 'game',
        };
    }

    /**
     * The media types of a library kind as search types led by all.
     *
     * @param int $kind
     *
     * @return array
     */
    protected function mediaTypes(int $kind): array
    {
        return MediaType::where('type', '=', $this->mediaTypeKind($kind))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), 'all')
            ->toArray();
    }
}
