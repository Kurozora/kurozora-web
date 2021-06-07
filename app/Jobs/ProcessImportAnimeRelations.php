<?php

namespace App\Jobs;

use App\Models\KDashboard\MediaRelated as KMediaRelated;
use App\Services\ImportAnimeRelationProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportAnimeRelations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of anime cast to process.
     *
     * @var Collection|KMediaRelated[] $kMediaRelated
     */
    protected Collection|array $kMediaRelated;

    /**
     * Create a new job instance.
     *
     * @param Collection|array $kMediaRelated
     */
    public function __construct(Collection|array $kMediaRelated)
    {
        $this->kMediaRelated = $kMediaRelated;
    }

    /**
     * Execute the job.
     *
     * @param ImportAnimeRelationProcessor $importAnimeRelationProcessor
     * @return void
     */
    public function handle(ImportAnimeRelationProcessor $importAnimeRelationProcessor)
    {
        $importAnimeRelationProcessor->process($this->kMediaRelated);
    }
}
