<?php

namespace App\Enums;

use App\Embeds\DefaultEmbed;
use App\Embeds\DoodStreamEmbed;
use App\Embeds\FembedEmbed;
use App\Embeds\FileMoonEmbed;
use App\Embeds\HydraxEmbed;
use App\Embeds\MixDropEmbed;
use App\Embeds\Mp4UploadEmbed;
use App\Embeds\StreamTapeEmbed;
use App\Embeds\YouTubeEmbed;
use BenSampo\Enum\Enum;

/**
 * @method static VideoSource Default()
 * @method static VideoSource DoodStream()
 * @method static VideoSource Fembed()
 * @method static VideoSource FileMoon()
 * @method static VideoSource Hydrax()
 * @method static VideoSource MixDrop()
 * @method static VideoSource Mp4Upload()
 * @method static VideoSource StreamTape()
 * @method static VideoSource YouTube()
 */
final class VideoSource extends Enum
{
    const Default       = DefaultEmbed::class;
    const DoodStream    = DoodStreamEmbed::class;
    const Fembed        = FembedEmbed::class;
    const FileMoon      = FileMoonEmbed::class;
    const Hydrax        = HydraxEmbed::class;
    const MixDrop       = MixDropEmbed::class;
    const Mp4Upload     = Mp4UploadEmbed::class;
    const StreamTape    = StreamTapeEmbed::class;
    const YouTube       = YouTubeEmbed::class;

    /**
     * Get the description for an enum value
     *
     * @param  mixed  $value
     * @return string
     */
    public static function getDescription($value): string
    {
        return match ($value) {
            self::YouTube => 'YouTube',
            self::DoodStream => 'DoodStream',
            self::Mp4Upload => 'Mp4Upload',
            self::StreamTape => 'StreamTape',
            self::FileMoon => 'FileMoon',
            self::MixDrop => 'MixDrop',
            default => parent::getDescription($value),
        };
    }
}
