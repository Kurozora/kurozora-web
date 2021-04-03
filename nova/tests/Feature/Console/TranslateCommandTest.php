<?php

namespace Laravel\Nova\Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Laravel\Nova\Tests\IntegrationTest;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;

class TranslateCommandTest extends IntegrationTest
{
    use InteractsWithPublishedFiles;

    protected $files = [
        'resources/lang/vendor/nova/en.json',
        'resources/lang/vendor/nova/nb.json',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpInteractsWithPublishedFiles();
        $this->prepareDirectories();
    }

    public function tearDown(): void
    {
        $this->tearDownInteractsWithPublishedFiles();

        parent::tearDown();
    }

    public function test_it_can_generate_translation_file()
    {
        $this->assertFilenameNotExists('resources/lang/vendor/nova/nb.json');

        $this->artisan('nova:translate', ['language' => 'nb'])->run();

        $this->assertFilenameExists('resources/lang/vendor/nova/nb.json');
        $this->assertFileContains(['Action'], 'resources/lang/vendor/nova/nb.json');
    }

    public function test_it_cant_override_generate_translation_file_without_force_option()
    {
        File::put(resource_path('lang/vendor/nova/nb.json'), '{}');

        $this->artisan('nova:translate', ['language' => 'nb'])->run();

        $this->assertFileNotContains(['Action'], 'resources/lang/vendor/nova/nb.json');
    }

    public function test_it_can_override_generate_translation_file_by_applying_force_option()
    {
        File::put(resource_path('lang/vendor/nova/nb.json'), '{}');

        $this->artisan('nova:translate', ['language' => 'nb', '--force' => true])->run();

        $this->assertFileContains(['Action'], 'resources/lang/vendor/nova/nb.json');
    }

    protected function prepareDirectories()
    {
        File::makeDirectory(resource_path('lang/vendor/nova'), 0755, true, true);
    }
}
