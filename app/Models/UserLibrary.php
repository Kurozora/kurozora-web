<?php

namespace App\Models;

use App\Enums\UserLibraryStatus;
use App\Scopes\SoftDeletingScopeBumpsUpdatedAt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class UserLibrary extends Pivot
{
    use Searchable,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'user_libraries';
    protected $table = self::TABLE_NAME;

    /**
     * The storage format of the model's date columns.
     *
     * @var string|null
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (UserLibrary $model) {
            $model->updateStatus($model->status);
        });
    }

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new SoftDeletingScopeBumpsUpdatedAt);
    }

    /**
     * Update the `started_at` and `ended_at` timestamps based on the new status.
     *
     * @param int $newStatus
     *
     * @return void
     */
    public function updateStatus(int $newStatus): void
    {
        switch ($newStatus) {
            case UserLibraryStatus::InProgress:
                $originalStatus = $this->getOriginal('status');
                $this->started_at = ($originalStatus == UserLibraryStatus::Planning || $this->started_at == null) ? now() : $this->started_at;
                $this->ended_at = null;
                break;
            case UserLibraryStatus::Dropped:
            case UserLibraryStatus::OnHold:
                $this->started_at = $this->started_at ?? now();
                $this->ended_at = null;
                break;
            case UserLibraryStatus::Completed:
                $this->started_at = $this->started_at ?? now();
                $this->ended_at = $this->ended_at ?? now();
                break;
            case UserLibraryStatus::Planning:
            default:
                $this->started_at = null;
                $this->ended_at = null;
        }
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes()
            ->with(['trackable' => function($query) {
                $query->with('translations');
            }]);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $trackable = $this->trackable;
        $library = $this->toArray();
        $library['trackable'] = [
            'slug' => $trackable->slug,
            'letter' => str_index($trackable->original_title),
            'original_title' => $trackable->original_title,
            'synonym_titles' => $trackable->synonym_titles,
            'title' => $trackable->title,
            'synopsis' => $trackable->synopsis,
            'tagline' => $trackable->tagline,
            'translations' => $trackable->translations,
            'country_id' => $trackable->country_id,
            'tv_rating_id' => $trackable->tv_rating_id,
            'media_type_id' => $trackable->media_type_id,
            'source_id' => $trackable->source_id,
            'status_id' => $trackable->status_id,
            'is_nsfw' => $trackable->is_nsfw,
            'started_at' => $trackable->started_at?->timestamp,
            'ended_at' => $trackable->ended_at?->timestamp,
        ];
        $library['started_at'] = $this->started_at?->timestamp;
        $library['ended_at'] = $this->ended_at?->timestamp;
        $library['created_at'] = $this->created_at?->timestamp;
        $library['updated_at'] = $this->updated_at?->timestamp;

        return $library;
    }

    /**
     * The trackable model the library belongs to.
     *
     * @return MorphTo
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes();
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
