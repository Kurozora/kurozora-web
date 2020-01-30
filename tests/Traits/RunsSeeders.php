<?php

namespace Tests\API\Traits;

use Illuminate\Support\Facades\Artisan;

trait RunsSeeders {
    /**
     * Seeds the database to be used in tests.
     *
     * @return void
     */
    protected function seedDatabase() {
        Artisan::call('db:seed');
    }
}
