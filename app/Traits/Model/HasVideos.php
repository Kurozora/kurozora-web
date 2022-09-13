<?php

namespace App\Traits\Model;

use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVideos
{
    /**
     * Get the model's videos.
     *
     * @return MorphMany
     */
    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    /**
     * Delete the model's videos.
     *
     * @return void
     */
    public function deleteVideos(): void
    {
        $this->videos()
            ->each(fn (Video $video) => $video->delete());
    }
}
