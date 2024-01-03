<?php

namespace App\Traits\Livewire;

trait WithReviewBox
{
    /**
     * The id of the review box.
     *
     * @var string $reviewBoxID
     */
    public string $reviewBoxID;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mountWithReviewBox(): void
    {
        $this->reviewBoxID = str()->random(20);
    }
}
