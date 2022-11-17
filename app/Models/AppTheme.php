<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Enums\StatusBarStyle;
use App\Enums\VisualEffectViewStyle;
use App\Traits\InteractsWithMediaExtension;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AppTheme extends KModel implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        Searchable,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'app_themes';
    protected $table = self::TABLE_NAME;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
//        'status_bar_style',
//        'visual_effect_view_style',
    ];

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Screenshot)
            ->singleFile();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $appTheme = $this->toArray();
        $appTheme['created_at'] = $this->created_at?->timestamp;
        $appTheme['updated_at'] = $this->updated_at?->timestamp;
        return $appTheme;
    }

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    public function pList(): string
    {
        $view = View::make('plist.ios-theme', [
            'theme' => $this
        ]);

        return $view->render();
    }

    /**
     * Generates a response to download the theme as a file.
     *
     * @return \Illuminate\Http\Response
     */
    public function download(): \Illuminate\Http\Response
    {
        // Name for the theme file
        $fileName = 'theme-' . $this->id . '.plist';

        $content = $this->pList();

        // Headers to return for the download
        $headers = [
            'Content-type'          => 'application/x-plist',
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $fileName),
            'Content-Length'        => strlen($content)
        ];

        // Return the file
        return Response::make($content, 200, $headers);
    }

    /**
     * The status bar style of the theme.
     *
     * @return StatusBarStyle
     */
    public function getStatusBarStyleAttribute(): StatusBarStyle
    {
        return StatusBarStyle::fromValue((int) $this->ui_status_bar_style);
    }

    /**
     * The visual effect view style of the theme.
     *
     * @return VisualEffectViewStyle
     */
    public function getVisualEffectViewStyleAttribute(): VisualEffectViewStyle
    {
        return VisualEffectViewStyle::fromValue((int) $this->ui_visual_effect_view);
    }
}
