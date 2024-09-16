<?php

namespace App\Jobs;

use App\Models\KDashboard\Character as KCharacter;
use App\Services\ImportCharacterProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportCharacter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The list of characters to process.
     *
     * @var Collection|KCharacter[] $kCharacters
     */
    protected Collection|array $kCharacters;

    /**
     * Create a new job instance.
     *
     * @param Collection|KCharacter[] $kCharacters
     */
    public function __construct(Collection|array $kCharacters)
    {
        $this->kCharacters = $kCharacters;
    }

    /**
     * Execute the job.
     *
     * @param ImportCharacterProcessor $importCharacterProcessor
     * @return void
     */
    public function handle(ImportCharacterProcessor $importCharacterProcessor)
    {
        $importCharacterProcessor->process($this->kCharacters);
    }
}
