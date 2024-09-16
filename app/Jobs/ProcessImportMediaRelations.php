<?php

namespace App\Jobs;

use App\Models\KDashboard\MediaRelated as KMediaRelated;
use App\Services\ImportMediaRelationProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportMediaRelations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of media cast to process.
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
     * @param ImportMediaRelationProcessor $importMediaRelationProcessor
     * @return void
     */
    public function handle(ImportMediaRelationProcessor $importMediaRelationProcessor): void
    {
        $importMediaRelationProcessor->process($this->kMediaRelated);
    }
}
