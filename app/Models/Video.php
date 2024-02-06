<?php

namespace App\Models;

use App\Enums\VideoSource;
use App\Enums\VideoType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends KModel
{
    use HasUlids,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'videos';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

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
