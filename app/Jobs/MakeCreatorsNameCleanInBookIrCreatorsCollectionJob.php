<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeCreatorsNameCleanInBookIrCreatorsCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $creator;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         $creatorName = trim($this->creator->xcreatorname);
        $this->creator->update([
           'xcreatorname' => $creatorName
        ]);
    }
}
