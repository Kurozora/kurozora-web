<?php

namespace App\Livewire\Misc;

use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Projects extends Component
{
    /**
     * The array of projects.
     *
     * @return void
     */
    public $projects = [];

    /**
     * Prepare the component.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function mount(): void
    {
        $projectsPath = resource_path('docs/projects.json');
        $projectsJSON = json_decode(File::get($projectsPath));
        $this->projects = $projectsJSON->projects;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.projects');
    }
}
