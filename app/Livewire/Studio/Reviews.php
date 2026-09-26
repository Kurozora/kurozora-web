<?php

namespace App\Livewire\Studio;

use App\Livewire\BaseReviews;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Model;

class Reviews extends BaseReviews
{
    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.studio.reviews';

    /**
     * Prepare the component.
     *
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Studio $studio): void
    {
        $this->studio = $studio->load(['media']);
    }

    /**
     * The studio the reviews belong to.
     *
     * @return Model
     */
    protected function reviewable(): Model
    {
        return $this->studio;
    }
}
