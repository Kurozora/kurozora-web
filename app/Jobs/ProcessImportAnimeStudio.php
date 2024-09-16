<?php

namespace App\Jobs;

use App\Models\KDashboard\AnimeProducer as KAnimeProducer;
use App\Services\ImportAnimeStudioProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnimeStudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of people to process.
     *
     * @var Collection|KAnimeProducer[] $kAnimeProducers
     */
    protected Collection|array $kAnimeProducers;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kAnimeProducers
     */
    public function __construct(Collection|array $kAnimeProducers)
    {
        $this->kAnimeProducers = $kAnimeProducers;
    }

    /**
     * Execute the job.
     *
     * @param ImportAnimeStudioProcessor $importAnimeStudioProcessor
     * @return void
     */
    public function handle(ImportAnimeStudioProcessor $importAnimeStudioProcessor): void
    {
        $importAnimeStudioProcessor->process($this->kAnimeProducers);
    }
}
