<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\MediaCollection;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\SlugOptions;

class Player extends KModel implements HasMedia
{
    use HasFactory,
        HasSlug,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'players';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'alternative_names' => AsArrayObject::class,
        ];
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('original_name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the activity options for activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Logo)
            ->singleFile();
    }
}
