<?php

namespace App\Console\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;
use Illuminate\Database\Migrations\Migrator;

class MigrateCommand extends BaseMigrateCommand
{
    use ConfirmableTrait;

    /**
     * Create a new migration command instance.
     *
     * @param Migrator $migrator
     * @return void
     */
    public function __construct(Migrator $migrator)
    {
        $this->signature .= "
                {--redownload : Indicates if the anime.json should be re-downloaded.}
        ";

        parent::__construct($migrator);
    }
}
