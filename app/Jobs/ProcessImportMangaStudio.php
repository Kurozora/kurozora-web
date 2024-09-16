<?php

namespace App\Jobs;

use App\Models\KDashboard\MangaMagazine as KMangaProducer;
use App\Services\ImportMangaStudioProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMangaStudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of manga producers to process.
     *
     * @var Collection|KMangaProducer[] $kMangaProducers
     */
    protected Collection|array $kMangaProducers;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kMangaProducers
     */
    public function __construct(Collection|array $kMangaProducers)
    {
        $this->kMangaProducers = $kMangaProducers;
    }

    /**
     * Execute the job.
     *
     * @param ImportMangaStudioProcessor $importMangaStudioProcessor
     * @return void
     */
    public function handle(ImportMangaStudioProcessor $importMangaStudioProcessor): void
    {
        $importMangaStudioProcessor->process($this->kMangaProducers);
    }
}
