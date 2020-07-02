<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static AnimeSource Unknown()
 * @method static AnimeSource Original()
 * @method static AnimeSource Book()
 * @method static AnimeSource PictureBook()
 * @method static AnimeSource Manga()
 * @method static AnimeSource DigitalManga()
 * @method static AnimeSource FourKomaManga()
 * @method static AnimeSource WebManga()
 * @method static AnimeSource Novel()
 * @method static AnimeSource LightNovel()
 * @method static AnimeSource VisualNovel()
 * @method static AnimeSource Game()
 * @method static AnimeSource CardGame()
 * @method static AnimeSource Music()
 * @method static AnimeSource Radio()
 */
final class AnimeSource extends Enum
{
    const Unknown       = 0;
    const Original      = 1;
    const Book          = 2;
    const PictureBook   = 3;
    const Manga         = 4;
    const DigitalManga  = 5;
    const FourKomaManga = 6;
    const WebManga      = 7;
    const Novel         = 8;
    const LightNovel    = 9;
    const VisualNovel   = 10;
    const Game          = 11;
    const CardGame      = 12;
    const Music         = 13;
    const Radio         = 14;
}
