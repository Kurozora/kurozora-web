<?php

namespace App\Models;

use App\Casts\AsArrayObject;
use App\Enums\MediaCollection;
use App\Scopes\PublicScope;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\SlugOptions;

class Provider extends KModel implements HasMedia
{
    use HasFactory,
        HasSlug,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Searchable,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'providers';
    protected $table = self::TABLE_NAME;

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new PublicScope);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'alternative_names' => AsArrayObject::class,
            'is_public' => 'boolean',
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

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_public;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $provider = $this->toArray();
        $provider['letter'] = str_index($this->original_name);
        $provider['created_at'] = $this->created_at?->timestamp;
        $provider['updated_at'] = $this->updated_at?->timestamp;
        return $provider;
    }
}
