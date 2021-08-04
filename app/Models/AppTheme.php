<?php

namespace App\Models;

use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasScreenshotImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\View;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AppTheme extends KModel implements HasMedia
{
    use HasFactory,
        HasScreenshotImage,
        InteractsWithMedia,
        InteractsWithMediaExtension;

    // Table name
    const TABLE_NAME = 'app_themes';
    protected $table = self::TABLE_NAME;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'screenshot_image',
        'screenshot_image_url',
        'status_bar_style',
        'visual_effect_view_style',
    ];

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    function pList(): string
    {
        $view = View::make('plist.ios-theme', [
            'theme'       => $this
        ]);

        return $view->render();
    }
}
