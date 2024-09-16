<?php

namespace App\Jobs;

use App\Models\KDashboard\MangaCharacter as KMangaCast;
use App\Services\ImportMangaCastProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMangaCast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of anime cast to process.
     *
     * @var Collection|KMangaCast[] $kMangaCasts
     */
    protected Collection|array $kMangaCasts;

    /**
     * Create a new job instance.
     *
     * @param Collection|KMangaCast[] $kMangaCasts
     */
    public function __construct(Collection|array $kMangaCasts)
    {
        $this->kMangaCasts = $kMangaCasts;
    }

    /**
     * Execute the job.
     *
     * @param ImportMangaCastProcessor $importMangaCastProcessor
     * @return void
     */
    public function handle(ImportMangaCastProcessor $importMangaCastProcessor): void
    {
        $importMangaCastProcessor->process($this->kMangaCasts);
    }
}
