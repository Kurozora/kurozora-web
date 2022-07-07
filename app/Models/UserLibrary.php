<?php

namespace App\Models;

use App\Enums\UserLibraryStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Scout\Searchable;

class UserLibrary extends Pivot
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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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
                case UserLibraryStatus::OnHold:
                case UserLibraryStatus::Watching:
                    $model->start_date = $model->start_date ?? now();
                    $model->end_date = null;
                    break;
                case UserLibraryStatus::Dropped:
                case UserLibraryStatus::Completed:
                    $model->start_date = $model->start_date ?? now();
                    $model->end_date = $model->end_date ?? now();
                    break;
                case UserLibraryStatus::Planning:
                default:
                    $model->start_date = null;
                    $model->end_date = null;
            }
        });
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $anime = $this->anime;
        $library = $this->toArray();
        $library['start_date'] = $this->start_date?->timestamp;
        $library['end_date'] = $this->end_date?->timestamp;
        $library['created_at'] = $this->created_at?->timestamp;
        $library['updated_at'] = $this->updated_at?->timestamp;
        $library['anime'] = [
            'slug' => $anime->slug,
            'original_title' => $anime->original_title,
            'synonym_titles' => $anime->synonym_titles,
            'title' => $anime->title,
            'synopsis' => $anime->synopsis,
            'tagline' => $anime->tagline,
            'translations' => $anime->translations,
        ];
        return $library;
    }

    /**
     * The anime the library belongs to.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
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
