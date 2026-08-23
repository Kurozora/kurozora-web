<?php

namespace App\Models;

use App\Enums\VideoSource;
use App\Enums\VideoType;
use App\Traits\Model\HasViews;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends KModel
{
    use HasViews, SoftDeletes;

    // Table name
    const string TABLE_NAME = 'videos';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_sub' => 'bool',
        'is_dub' => 'bool',
        'published_at' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Video $model) {
            // Auto order if necessary
            if (empty($model->order) && $model->order !== 0) {
                $model->order = Video::where([
                    ['videoable_type', '=', $model->videoable_type],
                    ['videoable_id', '=', $model->videoable_id],
                ])->max('order') + 1;
            }
        });
    }

    /**
     * Get the videoable entity that the video belongs to.
     *
     * @return MorphTo
     */
    public function videoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the language relationship of the video.
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the source of the video.
     *
     * @param string|null $value
     * @return VideoSource|null
     */
    public function getSourceAttribute(?string $value): ?VideoSource
    {
        return isset($value) ? VideoSource::fromValue($value) : null;
    }

    /**
     * Get the type of the video.
     *
     * @param int|null $value
     * @return VideoType
     */
    public function getTypeAttribute(?int $value): VideoType
    {
        return VideoType::fromValue((int) $value);
    }

    /**
     * Get the embed link for the respective source.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        $sourceClass = VideoSource::fromValue($this->source)->value;

        return (new $sourceClass($this))->getEmbed($data);
    }

    /**
     * Get the meta line of the video.
     *
     * @return string
     */
    public function getMetaLine(): string
    {
        $title = $this->videoable;

        if (empty($title)) {
            return '';
        }

        $genres = $title->genres
            ?->take(3)
            ->pluck('name')
            ->join(', ');

        $releaseDate = $title instanceof Game
            ? $title->published_at
            : $title->started_at;

        return collect([$genres, $releaseDate?->format('M Y')])
            ->filter()
            ->join(' · ');
    }

    /**
     * Get the embed link for the respective source.
     *
     * @return string
     */
    public function getUrl(): string
    {
        $sourceClass = VideoSource::fromValue($this->source)->value;

        return (new $sourceClass($this))->getUrl();
    }
}
