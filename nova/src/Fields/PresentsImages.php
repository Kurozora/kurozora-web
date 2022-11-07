<?php

namespace Laravel\Nova\Fields;

trait PresentsImages
{
    /**
     * The maximum width of the component.
     *
     * @var int|null
     */
    public $maxWidth = null;

    /**
     * The width of the component when presenting the field on the index view.
     *
     * @var int
     */
    public $indexWidth = 32;

    /**
     * The width of the component when presenting the field on the detail view.
     *
     * @var int
     */
    public $detailWidth = 128;

    /**
     * Indicates whether the image should be fully rounded or not.
     *
     * @var bool
     */
    public $rounded = false;

    /**
     * Indicates the aspect ratio class the image should be displayed with.
     *
     * @var string
     */
    public $aspect = 'aspect-auto';

    /**
     * Set the maximum width of the component.
     *
     * @param  int  $maxWidth
     * @return $this
     */
    public function maxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * Set the width of the image on the index view.
     *
     * @param  int  $width
     * @return $this
     */
    public function indexWidth($width)
    {
        $this->indexWidth = $width;

        return $this;
    }

    /**
     * Set the width of the image on the detail view.
     *
     * @param  int  $detailWidth
     * @return $this
     */
    public function detailWidth($detailWidth)
    {
        $this->detailWidth = $detailWidth;

        return $this;
    }

    /**
     * Display the image thumbnail with full-rounded edges.
     *
     * @return $this
     */
    public function rounded()
    {
        $this->rounded = true;

        return $this;
    }

    /**
     * Display the image thumbnail with square edges.
     *
     * @return $this
     */
    public function squared()
    {
        $this->rounded = false;

        return $this;
    }

    /**
     * Display the image thumbnail with square edges.
     *
     * @param  string  $aspect
     * @return $this
     */
    public function aspect($aspect)
    {
        $this->aspect = $aspect;

        return $this;
    }

    /**
     * Determine whether the field should have rounded corners.
     *
     * @return bool
     */
    public function isRounded()
    {
        return $this->rounded == true;
    }

    /**
     * Determine whether the field should have squared corners.
     *
     * @return bool
     */
    public function isSquared()
    {
        return $this->rounded == false;
    }

    /**
     * Return the attributes to present the image with.
     *
     * @return array
     */
    public function imageAttributes()
    {
        return [
            'indexWidth' => $this->indexWidth,
            'detailWidth' => $this->detailWidth,
            'maxWidth' => $this->maxWidth,
            'rounded' => $this->isRounded(),
            'aspect' => $this->aspect,
        ];
    }
}
