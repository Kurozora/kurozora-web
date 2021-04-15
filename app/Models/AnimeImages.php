<?php

namespace App\Models;

use ColorPalette;
use Illuminate\Database\Eloquent\Model;

class AnimeImages extends Model
{
    // Table name
    const TABLE_NAME = 'anime_images';
    protected $table = self::TABLE_NAME;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function(AnimeImages $animeImage) {
            if (!$animeImage->background_color || $animeImage->isDirty('url')) {
                if ($animeImage->isDirty('url'))
                    static::generateDimensionsFor($animeImage);

                static::generateColorsFor($animeImage);
            }

            return true;
        });
    }

    /**
     * Get the Anime belonging to the image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function anime() {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Generate colors for the given AnimeImage object.
     *
     * @param AnimeImages $animeImage
     */
    static function generateColorsFor(AnimeImages $animeImage) {
        $colors = ColorPalette::getPalette($animeImage->url, 5, 1, null);

        for($i = 0; $i < count($colors); $i++) {
            switch ($i) {
                case 0:
                    $animeImage->background_color = $colors[$i];
                    break;
                case 1:
                    $animeImage->text_color_1 = $colors[$i];
                    break;
                case 2:
                    $animeImage->text_color_2 = $colors[$i];
                    break;
                case 3:
                    $animeImage->text_color_3 = $colors[$i];
                    break;
                case 4:
                    $animeImage->text_color_4 = $colors[$i];
                    break;
            }
        }
    }

    /**
     * Generate dimensions for the given AnimeImage object.
     *
     * @param AnimeImages $animeImage
     */
    static function generateDimensionsFor(AnimeImages $animeImage) {
        list($width, $height) = getimagesize($animeImage->url);

        $animeImage->width = $width;
        $animeImage->height = $height;
    }
}
