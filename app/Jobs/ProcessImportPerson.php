<?php

namespace App\Jobs;

use App\Models\KDashboard\People as KPerson;
use App\Services\ImportPersonProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportPerson implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of people to process.
     *
     * @var Collection|KPerson[] $kPeople
     */
    protected Collection|array $kPeople;

    /**
     * Create a new job instance.
     *
     * @param Collection|KPerson[] $kPeople
     */
    public function __construct(Collection|array $kPeople)
    {
        $this->kPeople = $kPeople;
    }

    /**
     * Execute the job.
     *
     * @param ImportPersonProcessor $importPersonProcessor
     * @return void
     */
    public function handle(ImportPersonProcessor $importPersonProcessor)
    {
        $importPersonProcessor->process($this->kPeople);
    }
}
