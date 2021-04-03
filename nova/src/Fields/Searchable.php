<?php

namespace Laravel\Nova\Fields;

trait Searchable
{
    /**
     * Indicates if this relationship is searchable.
     *
     * @var bool|\Closure
     */
    public $searchable = false;

    /**
     * Indicates if the subtitle will be shown within search results.
     *
     * @var bool
     */
    public $withSubtitles = false;

    /**
     * The debounce amount to use when searching this field.
     *
     * @var int
     */
    public $debounce = 500;

    /**
     * Specify if the relationship should be searchable.
     *
     * @param  bool|\Closure  $searchable
     * @return $this
     */
    public function searchable($searchable = true)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Enable subtitles within the related search results.
     *
     * @return $this
     */
    public function withSubtitles()
    {
        $this->withSubtitles = true;

        return $this;
    }

    /**
     * Set the debounce period for use in searchable select inputs.
     *
     * @param int $amount
     * @return $this
     */
    public function debounce($amount)
    {
        $this->debounce = $amount;

        return $this;
    }
}
