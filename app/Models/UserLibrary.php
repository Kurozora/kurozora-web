<?php

namespace App\Models;

use App\Enums\UserLibraryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class UserLibrary extends KModel
{
    use Searchable;

    // Table name
    const TABLE_NAME = 'user_libraries';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (UserLibrary $model) {
            // Check if the anime needs an end date
            switch ($model->status) {
                case UserLibraryStatus::InProgress:
                case UserLibraryStatus::OnHold:
                    $model->started_at = $model->started_at ?? now();
                    $model->ended_at = null;
                    break;
                case UserLibraryStatus::Completed:
                case UserLibraryStatus::Dropped:
                    $model->started_at = $model->started_at ?? now();
                    $model->ended_at = $model->ended_at ?? now();
                    break;
                case UserLibraryStatus::Planning:
                default:
                    $model->started_at = null;
                    $model->ended_at = null;
            }
        });
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  Builder  $query
     * @return Builder
     */
    protected function makeAllSearchableUsing($query): Builder
    {
        return $query->withoutEagerLoads()
            ->withoutGlobalScopes();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $trackable = $this->trackable()
            ->withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with('translations')
            ->first();

        $library = $this->toArray();
        $library['trackable'] = [
            'slug' => $trackable->slug,
            'original_title' => $trackable->original_title,
            'synonym_titles' => $trackable->synonym_titles,
            'title' => $trackable->title,
            'synopsis' => $trackable->synopsis,
            'tagline' => $trackable->tagline,
            'translations' => $trackable->translations,
        ];
        $library['started_at'] = $this->started_at?->timestamp;
        $library['ended_at'] = $this->ended_at?->timestamp;
        $library['created_at'] = $this->created_at?->timestamp;
        $library['updated_at'] = $this->updated_at?->timestamp;

        return $library;
    }

    /**
     * The anime the library belongs to.
     *
     * @return MorphTo
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the library belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
