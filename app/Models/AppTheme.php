<?php

namespace App\Models;

use App\Traits\MediaLibraryExtensionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\View;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AppTheme extends KModel implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        MediaLibraryExtensionTrait;

    // Table name
    const TABLE_NAME = 'app_themes';
    protected $table = self::TABLE_NAME;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'screenshot'
    ];

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    function pList() {
        $view = View::make('plist.ios-theme', [
            'theme'       => $this
        ]);

        return $view->render();
    }

    /**
     * Returns the theme's screenshot.
     *
     * @return string
     */
    public function getScreenshotAttribute(): string
    {
        return $this->getFirstMediaFullUrl('screenshot');
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshot')
            ->singleFile();
    }
}
