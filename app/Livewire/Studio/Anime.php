<?php

namespace App\Livewire\Studio;

use App\Livewire\BaseMediaIndex;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Anime extends BaseMediaIndex
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
    protected string $view = 'livewire.studio.anime';

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
     * The query returning the anime the studio produced.
     *
     * @return Builder|Relation
     */
    protected function query(): Builder|Relation
    {
        return $this->studio->anime();
    }
}
