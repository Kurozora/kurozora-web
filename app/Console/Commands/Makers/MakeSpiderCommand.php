<?php

namespace App\Console\Commands\Makers;

use Illuminate\Console\Command;

class MakeSpiderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:spider {name : The name of the class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new spider class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        return $this->call('roach:spider', ['name' => $name]);
    }
}
