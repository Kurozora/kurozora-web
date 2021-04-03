<?php

namespace App\Models;

use App\Traits\MediaLibraryExtensionTrait;
use Illuminate\Support\Facades\View;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AppTheme extends KModel implements HasMedia
{
    use InteractsWithMedia,
        MediaLibraryExtensionTrait;

    // Table name
    const TABLE_NAME = 'app_themes';
    protected $table = self::TABLE_NAME;

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
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshot')
            ->singleFile();
    }
}
